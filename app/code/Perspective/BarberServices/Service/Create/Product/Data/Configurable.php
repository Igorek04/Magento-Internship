<?php
namespace Perspective\BarberServices\Service\Create\Product\Data;

use Exception;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Api\Data\OptionInterfaceFactory;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Perspective\BarberServices\Service\Create\Attribute as AttributeService;
use Psr\Log\LoggerInterface;

class Configurable
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var ProductExtensionFactory
     */
    protected $extensionFactory;
    /**
     * @var AttributeService
     */
    protected $attributeService;
    /**
     * @var OptionInterfaceFactory
     */
    protected $optionFactory;
    /**
     * @var OptionValueInterfaceFactory
     */
    protected $optionValueFactory;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ProductExtensionFactory $extensionFactory
     * @param AttributeService $attributeService
     * @param OptionInterfaceFactory $optionFactory
     * @param OptionValueInterfaceFactory $optionValueFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductExtensionFactory $extensionFactory,
        AttributeService $attributeService,
        OptionInterfaceFactory $optionFactory,
        OptionValueInterfaceFactory $optionValueFactory,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->extensionFactory = $extensionFactory;
        $this->attributeService = $attributeService;
        $this->optionFactory = $optionFactory;
        $this->optionValueFactory = $optionValueFactory;
        $this->logger = $logger;
    }

    /**
     * Set configurable product data
     *
     * @param ProductInterface $product
     * @param array $childSkus
     * @param int $setId
     * @return void
     */
    public function setConfigurableProductData(ProductInterface $product, array $childSkus, int $setId): void
    {
        $product->setPrice(0);
        $product->setVisibility(Visibility::VISIBILITY_BOTH);
        $product->setStockData(['is_in_stock' => 1]);

        $childProducts = [];
        foreach ($childSkus as $sku) {
            try {
                $childProducts[] = $this->productRepository->get($sku);
            } catch (NoSuchEntityException $e) {
                $this->logger->error(__('Could not find child product with SKU %1', $sku));
            }
        }

        $extensionAttributes = $product->getExtensionAttributes() ?: $this->extensionFactory->create();
        $extensionAttributes->setConfigurableProductOptions($this->getConfigurableOptionsData($childProducts, $setId));
        $product->setExtensionAttributes($extensionAttributes);
    }

    /**
     * @param array $childProducts
     * @param int $setId
     * @return array
     */
    private function getConfigurableOptionsData(array $childProducts, int $setId): array
    {
        $options = [];
        $position = 0;
        $attributesInSet = $this->attributeService->getAttributesBySetId($setId);

        foreach ($attributesInSet as $code => $id) {
            try {
                $uniqueOptionIds = [];
                foreach ($childProducts as $childProduct) {
                    $val = $childProduct->getData($code);
                    if ($val) {
                        $uniqueOptionIds[$val] = true;
                    }
                }

                if (empty($uniqueOptionIds)) {
                    continue;
                }

                $optionValues = [];
                foreach (array_keys($uniqueOptionIds) as $optionValueId) {
                    $valueObj = $this->optionValueFactory->create();
                    $valueObj->setValueIndex($optionValueId);
                    $optionValues[] = $valueObj;
                }

                $attribute = $this->attributeService->getAttributeByCode($code);
                $option = $this->optionFactory->create();
                $option->setAttributeId($attribute->getAttributeId())
                    ->setLabel($attribute->getDefaultFrontendLabel())
                    ->setPosition($position++)
                    ->setValues($optionValues);

                $options[] = $option;
            } catch (Exception $e) {
                $this->logger->error(__('Configurable option error: %1. Error: %2', $code, $e->getMessage()));
            }
        }
        return $options;
    }
}
