<?php
namespace Perspective\Voting\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;

class ProductList implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options[] = [
            'label' => __('-- Please Select a Product --'),
            'value' => ''
        ];
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect(['name', 'sku'])
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', Visibility::VISIBILITY_BOTH)
            ->addAttributeToSort('sku', 'ASC');

        foreach ($collection as $product) {
            $options[] = [
                'label' => sprintf('%s (%s)', $product->getSku(), $product->getName()),
                'value' => $product->getId(),
            ];
        }
        return $options;
    }
}
