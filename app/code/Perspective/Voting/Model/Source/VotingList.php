<?php
namespace Perspective\Voting\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Perspective\Voting\Model\ResourceModel\Voting\CollectionFactory;

class VotingList implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
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
