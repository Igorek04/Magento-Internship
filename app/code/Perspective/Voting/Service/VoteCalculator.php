<?php
namespace Perspective\Voting\Service;

use Perspective\Voting\Model\ResourceModel\VotingVote\CollectionFactory as VoteCollectionFactory;
use Perspective\Voting\Model\VotingOptionManager;
use Perspective\Voting\Service\ConfigData;
class VoteCalculator
{
    /**
     * @var VoteCollectionFactory
     */
    protected $voteCollectionFactory;
    /**
     * @var VotingOptionManager
     */
    protected $votingOptionManager;
    /**
     * @var ConfigData
     */
    protected $configDataService;

    /**
     * @param VoteCollectionFactory $voteCollectionFactory
     * @param VotingOptionManager $votingOptionManager
     * @param ConfigData $configDataService
     */
    public function __construct(
        VoteCollectionFactory $voteCollectionFactory,
        VotingOptionManager $votingOptionManager,
        ConfigData $configDataService,
    ) {
        $this->voteCollectionFactory = $voteCollectionFactory;
        $this->votingOptionManager = $votingOptionManager;
        $this->configDataService = $configDataService;
    }

    /**
     * Get the real count of user votes for each option in a specific voting
     *
     * @param int $votingId
     * @return array
     */
    public function getRawVotes(int $votingId): array
    {
        $collection = $this->voteCollectionFactory->create();
        $collection->addFieldToFilter('voting_id', $votingId);

        $allOptionIds = $collection->getColumnValues('option_id');
        return array_count_values($allOptionIds);
    }

    /**
     * Calculate total votes including user votes and additional admin votes
     *
     * @param int $votingId
     * @return array
     */
    public function getFinalVotesByVotingId(int $votingId): array
    {
        // get user votes by voting
        $rawVotes = $this->getRawVotes($votingId);

        $isAdminVotesEnabled = $this->configDataService->isAdminAllowedEditVotes();

        $options = $this->votingOptionManager->getOptionsByVotingId($votingId);

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
