<?php
namespace Perspective\Voting\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Perspective\Voting\Model\ResourceModel\Voting\CollectionFactory;

class VotingList implements OptionSourceInterface
{
    protected $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $collection = $this->collectionFactory->create();
        $options = [];

        foreach ($collection as $voting) {
            $options[] = [
                'value' => $voting->getId(),
                'label' => $voting->getTitle()
            ];
        }
        return $options;
    }
}
