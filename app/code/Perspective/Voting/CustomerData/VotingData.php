<?php
namespace Perspective\Voting\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Perspective\Voting\Service\UserIdentification;
use Perspective\Voting\Model\ResourceModel\VotingVote\CollectionFactory;

class VotingData implements SectionSourceInterface
{
    /**
     * @var UserIdentification
     */
    protected $userIdentification;
    /**
     * @var CollectionFactory
     */
    protected $voteCollectionFactory;

    /**
     * @param UserIdentification $userIdentification
     * @param CollectionFactory $voteCollectionFactory
     */
    public function __construct(
        UserIdentification $userIdentification,
        CollectionFactory $voteCollectionFactory
    ) {
        $this->userIdentification = $userIdentification;
        $this->voteCollectionFactory = $voteCollectionFactory;
    }

    /**
     * Get customer or guest voting history(private section data) for UI pre-selection
     *
     * @return array
     */
    public function getSectionData(): array
    {
        $identity = $this->userIdentification->initIdentityData();
        $customerId = $identity['customer_id'];
        $guestHash = $identity['guest_hash'];

        $collection = $this->voteCollectionFactory->create();

        if ($customerId) {
            $collection->addFieldToFilter('customer_id', $customerId);
        } else {
            $collection->addFieldToFilter('guest_hash', $guestHash);
        }

        $votes = [];
        foreach ($collection as $vote) {
            $votes[$vote->getVotingId()] = (int)$vote->getOptionId();
        }

        return [
            'votes' => $votes
        ];
    }
}
