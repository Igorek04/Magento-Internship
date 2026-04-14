<?php
namespace Perspective\BarberServices\Service\Create;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Perspective\BarberServices\Service\Create\AttributeSet as AttributeSetService;
use Perspective\BarberServices\Service\Create\Product\Linker as ProductLinker;
use Perspective\BarberServices\Service\Create\Product\Data\Simple as SimpleDataService;
use Perspective\BarberServices\Service\Create\Product\Data\Configurable as ConfigurableDataService;

class Product
{
    /**
     * @var ProductInterfaceFactory
     */
    protected $productFactory;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var AttributeSetService
     */
    protected $attributeSetService;
    /**
     * @var ProductLinker
     */
    protected $productLinker;
    /**
     * @var SimpleDataService
     */
    protected $simpleDataService;
    /**
     * @var ConfigurableDataService
     */
    protected $configurableDataService;

    /**
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param AttributeSetService $attributeSetService
     * @param ProductLinker $productLinker
     * @param SimpleDataService $simpleDataService
     * @param ConfigurableDataService $configurableDataService
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        AttributeSetService $attributeSetService,
        ProductLinker $productLinker,
        SimpleDataService $simpleDataService,
        ConfigurableDataService $configurableDataService
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->attributeSetService = $attributeSetService;
        $this->productLinker = $productLinker;
        $this->simpleDataService = $simpleDataService;
        $this->configurableDataService = $configurableDataService;
    }

    /**
     * @param array $data
     * @param array $childSkus
     * @return string|null
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     */
    public function execute(array $data, array $childSkus = []): ?string
    {
        // update or create
        $product = $this->initProduct($data['sku']);
        $setId = $this->attributeSetService->getAttributeSetIdByName($data['attribute_set']);

        // base data
        $this->setBaseProductData($product, $data, $setId);

        if ($data['type'] === Configurable::TYPE_CODE) {
            // configurable
            $this->configurableDataService->setConfigurableProductData($product, $childSkus, $setId);
        } else {
            // simple
            $this->simpleDataService->setSimpleProductData($product, $data, $setId);
        }

        $this->productRepository->save($product);

        // links (categories, configurable children)
        $this->productLinker->linkCategory($product, $data['categories']);
        if ($data['type'] === Configurable::TYPE_CODE) {
            $this->productLinker->linkConfigurableChildren($product, $childSkus);
        }

        return $data['parent_sku'] ?? null;
    }

    /**
     * Load or create product
     *
     * @param string $sku
     * @return ProductInterface
     */
    private function initProduct(string $sku): ProductInterface
    {
        try {
            return $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            return $this->productFactory->create();
        }
    }

    /**
     * Set common data
     *
     * @param ProductInterface $product
     * @param array $data
     * @param int $setId
     * @return void
     */
    private function setBaseProductData(ProductInterface $product, array $data, int $setId): void
    {
        $urlKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name'] . '-' . $data['sku']), '-'));

        $product->setStoreId(0)
            ->setWebsiteIds([1])
            ->setSku($data['sku'])
            ->setName($data['name'])
            ->setData('url_key', $urlKey)
            ->setAttributeSetId($setId)
            ->setTypeId($data['type'])
            ->setStatus(Status::STATUS_ENABLED)
            ->setData('save_rewrites_history', false);
    }
}
