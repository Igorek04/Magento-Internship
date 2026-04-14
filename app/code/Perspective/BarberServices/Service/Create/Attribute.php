<?php
namespace Perspective\BarberServices\Service\Create;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Perspective\BarberServices\Service\Create\AttributeSet as AttributeSetService;

class Attribute
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;
    /**
     * @var AttributeSetService
     */
    protected $attributeSetService;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CollectionFactory $collectionFactory
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param AttributeSetService $attributeSetService
     * @param LoggerInterface $logger
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        CollectionFactory $collectionFactory,
        ProductAttributeRepositoryInterface $attributeRepository,
        AttributeSetService $attributeSetService,
        LoggerInterface $logger
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->collectionFactory = $collectionFactory;
        $this->attributeRepository = $attributeRepository;
        $this->attributeSetService = $attributeSetService;
        $this->logger = $logger;
    }

    /**
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function execute(array $data): void
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $groupName = 'Barber Services';
        $this->attributeSetService->createAttributeSet($data['attribute_set'], $entityTypeId, $eavSetup);
        $this->saveAttribute($data, $eavSetup, $groupName);
        $eavSetup->addAttributeToSet(Product::ENTITY, $data['attribute_set'], $groupName, $data['attribute_code']);
    }

    /**
     * @param array $data
     * @param $eavSetup
     * @param string $groupName
     * @return void
     */
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

    /**
     * @param int $setId
     * @return array
     */
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

    /**
     * @param string $attributeCode
     * @param string $label
     * @return int|null
     */
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
            $this->logger->error(__('BarberServices Attribute Service: Attribute "%1" does not exist.', $attributeCode));
            return null;
        }
        $this->logger->warning(__('BarberServices Attribute Service: Option with label "%1" not found for attribute "%2".', $label, $attributeCode));
        return null;
    }

    /**
     * @param string $attributeCode
     * @return ProductAttributeInterface
     * @throws NoSuchEntityException
     */
    public function getAttributeByCode(string $attributeCode)
    {
        return $this->attributeRepository->get($attributeCode);
    }
}
