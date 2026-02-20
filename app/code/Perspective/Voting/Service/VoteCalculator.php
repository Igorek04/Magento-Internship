<?php

namespace Perspective\Voting\Service;

use Perspective\Voting\Model\ResourceModel\VotingVote\CollectionFactory as VoteCollectionFactory;
use Perspective\Voting\Model\VotingOptionManager;
use Perspective\Voting\Service\ConfigData;
use Perspective\Voting\Model\ResourceModel\VotingOption\CollectionFactory as OptionCollectionFactory;


class VoteCalculator
{
    protected $voteCollectionFactory;
    protected $votingOptionManager;
    protected $configDataService;
    protected $optionCollectionFactory;

    public function __construct(
        VoteCollectionFactory $voteCollectionFactory,
        VotingOptionManager $votingOptionManager,
        ConfigData $configDataService,
        OptionCollectionFactory $optionCollectionFactory
    ) {
        $this->voteCollectionFactory = $voteCollectionFactory;
        $this->votingOptionManager = $votingOptionManager;
        $this->configDataService = $configDataService;
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    public function getRawVotes(int $votingId): array
    {
        $collection = $this->voteCollectionFactory->create();
        $collection->addFieldToFilter('voting_id', $votingId);

        $allOptionIds = $collection->getColumnValues('option_id');
        return array_count_values($allOptionIds);
    }

    public function getFinalVotesByVotingId(int $votingId): array
    {
        // get user votes by voting
        $rawVotes = $this->getRawVotes($votingId);

        $isAdminVotesEnabled = $this->configDataService->isAdminAllowedEditVotes();

        $options = $this->optionCollectionFactory->create();
        $options->addFieldToFilter('voting_id', $votingId);

        $finalVotes = [];
        foreach ($options as $option) {
            $optionId = (int)$option->getId();

            // get user votes for option ( if not have votes - set 0)
            $total = $rawVotes[$optionId] ?? 0;

            // if allowed admin editing - add admin votes
            if ($isAdminVotesEnabled) {
                $total += $option->getAdditionalAdminVotes();
            }

            $finalVotes[$optionId] = $total;
        }

        arsort($finalVotes);
        return $finalVotes;
    }
}
