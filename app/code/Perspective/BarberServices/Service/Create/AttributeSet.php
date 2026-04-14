<?php
namespace Perspective\BarberServices\Service\Create;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Model\Config as EavConfig;

class AttributeSet
{
    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;
    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param AttributeSetFactory $attributeSetFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param EavConfig $eavConfig
     */
    public function __construct(
        AttributeSetRepositoryInterface $attributeSetRepository,
        AttributeSetFactory $attributeSetFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EavConfig $eavConfig
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param string $setName
     * @param int $entityTypeId
     * @param $eavSetup
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function createAttributeSet(string $setName, int $entityTypeId, $eavSetup): void
    {
        //check if exists to avoid duplicates
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('attribute_set_name', $setName)
            ->addFilter('entity_type_id', $entityTypeId)
            ->create();
        if ($this->attributeSetRepository->getList($searchCriteria)->getTotalCount() > 0) return;

        //create attribute set
        $defaultSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $attributeSet = $this->attributeSetFactory->create()->setData([
            'attribute_set_name' => $setName,
            'entity_type_id' => $entityTypeId,
            'sort_order' => 100
        ]);
        $this->attributeSetRepository->save($attributeSet);
        $attributeSet->initFromSkeleton($defaultSetId);
        $this->attributeSetRepository->save($attributeSet);
    }

    /**
     * @param string $setName
     * @return int
     * @throws LocalizedException
     */
    public function getAttributeSetIdByName(string $setName): int
    {
        $entityTypeId = $this->eavConfig->getEntityType(Product::ENTITY)->getEntityTypeId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('attribute_set_name', $setName)
            ->addFilter('entity_type_id', $entityTypeId)
            ->create();

        $list = $this->attributeSetRepository->getList($searchCriteria);
        if ($list->getTotalCount() === 0) {
            throw new LocalizedException(__("Attribute Set '%1' not found.", $setName));
        }

        $items = $list->getItems();
        return reset($items)->getAttributeSetId();
    }
}
