<?php
namespace Perspective\CartBonus\Model\Config\Source;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
class ProductList implements \Magento\Framework\Option\ArrayInterface
{
    protected $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('id')
            ->addAttributeToFilter('type_id', 'simple')
            ->setOrder('name', 'ASC');

        $options = [];
        foreach ($collection as $product) {
            $options[] = [
                'value' => $product->getId(),
                'label' => $product->getName()
            ];
        }
        return $options;
    }
}
