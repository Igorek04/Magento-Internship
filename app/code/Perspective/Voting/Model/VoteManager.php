<?php

namespace Perspective\Voting\Model;

use Perspective\Voting\Model\ResourceModel\VotingVote as VoteResourceModel;
use Perspective\Voting\Model\ResourceModel\VotingVote\CollectionFactory as VoteCollectionFactory;
use Perspective\Voting\Model\VotingManager;
use Perspective\Voting\Service\VotingValidation;
use Perspective\Voting\Service\UserIdentification;


class VoteManager
{
    protected $voteCollectionFactory;
    protected $voteResourceModel;
    protected $votingManager;
    protected $votingValidationService;
    protected $userIdentificationService;
    public function __construct(
        VoteCollectionFactory $voteCollectionFactory,
        VoteResourceModel $voteResourceModel,
        VotingManager $votingManager,
        VotingValidation $votingValidationService,
        UserIdentification $userIdentificationService
    ) {
        $this->voteCollectionFactory = $voteCollectionFactory;
        $this->voteResourceModel = $voteResourceModel;
        $this->votingManager = $votingManager;
        $this->votingValidationService = $votingValidationService;
        $this->userIdentificationService = $userIdentificationService;
    }

    public function getUserVoteByVoting(int $votingId, $customerId, $guestHash = null)
    {
        $collection = $this->voteCollectionFactory->create();
        $collection->addFieldToFilter('voting_id', $votingId);

        if ($customerId) {
            $collection->addFieldToFilter('customer_id', $customerId);
        } else {
            $collection->addFieldToFilter('guest_hash', $guestHash);
        }

        return $collection->getFirstItem();
    }

    public function getVotingIdsByUserId($customerId)
    {
        $collection = $this->voteCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        return array_unique($collection->getColumnValues('voting_id'));
    }


    public function processVote(int $votingId, int $optionId, array $identity): string
    {
        // voting validation
        $voting = $this->votingManager->getById($votingId);
        $this->votingValidationService->canVote($voting, $identity);

        // user data
        $customerId = $identity['customer_id'];
        $guestHash = $identity['guest_hash'];

        // load/create vote
        $vote = $this->getUserVoteByVoting($votingId, $customerId, $guestHash);
        $isNewVote = !$vote->getId();

        // save/update vote
        $vote->setVotingId($votingId);
        $vote->setOptionId($optionId);
        if ($customerId) {
            $vote->setCustomerId($customerId);
        } else {
            $vote->setGuestHash($guestHash);
        }
        $this->voteResourceModel->save($vote);

        //
        if ($isNewVote) {
            $message = __('Your vote has been successfully counted. Statistics will be updated soon.');
        } else {
            $message = __('Your vote has been successfully updated. Statistics will be updated soon.');
        }
        return $message;
    }

    public function convertGuestVotesToCustomer($guestHash, $customerId)
    {
        $guestVoteCollection = $this->voteCollectionFactory->create();
        $guestVoteCollection->addFieldToFilter('guest_hash', $guestHash);

        foreach ($guestVoteCollection as $vote) {
            $votingId = $vote->getVotingId();

            if ($this->getUserVoteByVoting($votingId, $customerId)->getId()) {
                $this->voteResourceModel->delete($vote);
            } else {
                $vote->setCustomerId($customerId);
                $vote->setGuestHash(null);
                $this->voteResourceModel->save($vote);
            }
        }
    }
}
