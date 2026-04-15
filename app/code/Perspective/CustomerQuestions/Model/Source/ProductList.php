<?php
namespace Perspective\CustomerQuestions\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class ProductList implements OptionSourceInterface
{
    protected $productCollectionFactory;

    protected $productVisibility;

    protected $productStatus;

    public function __construct(
        CollectionFactory $productCollectionFactory,
        Visibility $productVisibility,
        Status $productStatus
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
        $this->productStatus = $productStatus;
    }

    public function toOptionArray()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('name');

        $collection->addAttributeToFilter('status', ['eq' => Status::STATUS_ENABLED]);
        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());

        $options = [];
        foreach ($collection as $product) {
            $options[] = [
                'value' => $product->getId(),
                'label' => sprintf('%s (ID: %s)', $product->getName(), $product->getId())
            ];
        }
        return $options;
    }
}
