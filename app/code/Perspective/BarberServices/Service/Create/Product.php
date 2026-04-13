<?php

namespace Perspective\BarberServices\Service\Create;

use Exception;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Api\Data\OptionInterfaceFactory;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Perspective\BarberServices\Service\Create\Category as CategoryService;
use Perspective\BarberServices\Service\Create\Attribute as AttributeService;
use Psr\Log\LoggerInterface;

class Product
{
    protected $productFactory;
    protected $productRepository;
    protected $categoryService;
    protected $attributeService;
    protected $logger;
    protected $extensionFactory;
    protected $optionFactory;
    protected $optionValueFactory;
    protected $linkManagement;
    protected $categoryLinkManagement;

    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CategoryService $categoryService,
        AttributeService $attributeService,
        ProductExtensionFactory $extensionFactory,
        OptionInterfaceFactory $optionFactory,
        OptionValueInterfaceFactory $optionValueFactory,
        LinkManagementInterface $linkManagement,
        CategoryLinkManagementInterface $categoryLinkManagement,
        LoggerInterface $logger
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryService = $categoryService;
        $this->attributeService = $attributeService;
        $this->extensionFactory = $extensionFactory;
        $this->optionFactory = $optionFactory;
        $this->optionValueFactory = $optionValueFactory;
        $this->linkManagement = $linkManagement;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->logger = $logger;
    }

    public function execute(array $data, array $childSkus = []): ?string
    {
        // update or create
        try {
            $product = $this->productRepository->get($data['sku']);
        } catch (NoSuchEntityException $e) {
            $product = $this->productFactory->create();
        }

        $categoryId = $this->categoryService->getCategoryIdByPath($data['categories']);
        $setId = $this->attributeService->getAttributeSetIdByName($data['attribute_set']);
        $urlKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['name'] . '-' . $data['sku']), '-'));

        // common product data
        $product->setStoreId(0)
            ->setWebsiteIds([1])
            ->setSku($data['sku'])
            ->setName($data['name'])
            ->setData('url_key', $urlKey)
            ->setAttributeSetId($setId)
            ->setTypeId($data['type'])
            ->setStatus(Status::STATUS_ENABLED)
            ->setData('save_rewrites_history', false);

        //configurable
        if ($data['type'] === Configurable::TYPE_CODE) {
            $product->setPrice(0);
            $product->setVisibility(Visibility::VISIBILITY_BOTH);
            $product->setStockData(['is_in_stock' => 1]);

            $childProducts = [];
            foreach ($childSkus as $sku) {
                try {
                    $childProducts[] = $this->productRepository->get($sku);
                } catch (Exception $e) {
                    $this->logger->error(sprintf('Could not find child product with SKU %s', $sku));
                }
            }

            $extensionAttributes = $product->getExtensionAttributes() ?: $this->extensionFactory->create();
            $extensionAttributes->setConfigurableProductOptions($this->getConfigurableAttributesData($childProducts, $setId));
            $product->setExtensionAttributes($extensionAttributes);
        } else {
            //simple
            $product->setPrice($data['price']);
            $product->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);
            $product->setStockData(['use_config_manage_stock' => 0, 'is_in_stock' => 1, 'qty' => 999]);

            foreach ($this->attributeService->getAttributesBySetId($setId) as $code => $id) {
                if (!empty($data[$code])) {
                    $optionId = $this->attributeService->getOptionIdByLabel($code, $data[$code]);
                    if ($optionId !== null) {
                        $product->setData($code, $optionId);
                    }
                }
            }
        }

        $this->productRepository->save($product);

        try {
            $this->categoryLinkManagement->assignProductToCategories($data['sku'], [$categoryId]);
        } catch (Exception $e) {
            $this->logger->error(sprintf('Failed category link: %s', $data['sku']));
        }

        if ($data['type'] === Configurable::TYPE_CODE && !empty($childSkus)) {
            $existingChildren = $this->linkManagement->getChildren($product->getSku());
            $existingSkus = [];
            foreach ($existingChildren as $child) {
                $existingSkus[] = $child->getSku();
            }

            foreach ($childSkus as $childSku) {
                if (in_array($childSku, $existingSkus)) {
                    continue;
                }

                try {
                    $this->linkManagement->addChild($product->getSku(), $childSku);
                } catch (Exception $e) {
                    $this->logger->error(sprintf('Failed child link: %s to %s', $childSku, $product->getSku()));
                }
            }
        }

        return $data['parent_sku'] ?? null;
    }

    private function getConfigurableAttributesData(array $childProducts, int $setId): array
    {
        $options = [];
        $position = 0;
        $attributesInSet = $this->attributeService->getAttributesBySetId($setId);

        foreach ($attributesInSet as $code => $id) {
            try {
                $uniqueOptionIds = [];
                foreach ($childProducts as $childProduct) {
                    if ($val = $childProduct->getData($code)) {
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
                $this->logger->error('Configurable option error: ' . $code);
            }
        }
        return $options;
    }
}
