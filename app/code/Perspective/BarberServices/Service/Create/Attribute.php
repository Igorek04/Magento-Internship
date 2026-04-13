<?php

namespace Perspective\BarberServices\Service\Create;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class Attribute
{
    protected $eavSetupFactory;
    protected $moduleDataSetup;
    protected $attributeSetFactory;
    protected $attributeSetRepository;
    protected $searchCriteriaBuilder;
    protected $collectionFactory;
    protected $attributeSetCollectionFactory;
    protected $logger;
    protected $attributeRepository;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        AttributeSetFactory $attributeSetFactory,
        AttributeSetRepositoryInterface $attributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $collectionFactory,
        AttributeSetCollectionFactory $attributeSetCollectionFactory,
        ProductAttributeRepositoryInterface $attributeRepository,
        LoggerInterface $logger
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
    }

    public function execute(array $data): void
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $groupName = 'Barber Services';
        if (!$this->isAttributeSetExists($data['attribute_set'], $entityTypeId)) {
            $this->createAttributeSet($data['attribute_set'], $entityTypeId, $eavSetup);
        }
        $this->saveAttribute($data, $eavSetup, $groupName);
        $eavSetup->addAttributeToSet(Product::ENTITY, $data['attribute_set'], $groupName, $data['attribute_code']);
    }

    private function saveAttribute(array $data, $eavSetup, string $groupName): void
    {
        $eavSetup->addAttribute(Product::ENTITY, $data['attribute_code'], [
            'type' => 'int',
            'label' => $data['label'],
            'input' => $data['type'],
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'option' => ['values' => array_map('trim', explode(',', $data['options']))],
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group' => $groupName
        ]);
    }

    private function isAttributeSetExists(string $setName, int $entityTypeId): bool
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('attribute_set_name', $setName)
            ->addFilter('entity_type_id', $entityTypeId)
            ->create();
        $list = $this->attributeSetRepository->getList($searchCriteria);
        return $list->getTotalCount() > 0;
    }

    private function createAttributeSet(string $setName, int $entityTypeId, $eavSetup): void
    {
        $defaultSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $attributeSet = $this->attributeSetFactory->create();
        $attributeSet->setData([
            'attribute_set_name' => $setName,
            'entity_type_id' => $entityTypeId,
            'sort_order' => 100
        ]);
        $this->attributeSetRepository->save($attributeSet);
        $attributeSet->initFromSkeleton($defaultSetId);
        $this->attributeSetRepository->save($attributeSet);
    }

    public function getAttributeSetIdByName(string $setName): int
    {
        $collection = $this->attributeSetCollectionFactory->create()
            ->addFieldToFilter('attribute_set_name', $setName)
            ->setPageSize(1);
        $attributeSet = $collection->getFirstItem();
        if (!$attributeSet->getId()) {
            throw new LocalizedException(__("Attribute Set '%1' not found.", $setName));
        }
        return (int)$attributeSet->getId();
    }

    public function getAttributesBySetId(int $setId): array
    {
        $collection = $this->collectionFactory->create()
            ->setAttributeSetFilter($setId)
            ->addFieldToFilter('is_user_defined', 1);
        $attributes = [];
        foreach ($collection as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute->getAttributeId();
        }
        return $attributes;
    }

    public function getOptionIdByLabel(string $attributeCode, string $label): ?int
    {
        try {
            $attribute = $this->attributeRepository->get($attributeCode);
            $options = $attribute->getOptions();
            foreach ($options as $option) {
                if (trim($option->getLabel()) === trim($label)) {
                    if ($option->getValue() === '') {
                        continue;
                    }
                    return (int)$option->getValue();
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error(sprintf('BarberServices Attribute Service: Attribute "%s" does not exist.', $attributeCode));
            return null;
        }
        $this->logger->warning(sprintf('BarberServices Attribute Service: Option with label "%s" not found for attribute "%s".', $label, $attributeCode));
        return null;
    }

    /**
     * НОВЫЙ МЕТОД
     * @param string $attributeCode
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     * @throws NoSuchEntityException
     */
    public function getAttributeByCode(string $attributeCode)
    {
        return $this->attributeRepository->get($attributeCode);
    }
}
