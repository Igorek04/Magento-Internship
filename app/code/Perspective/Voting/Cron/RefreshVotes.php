<?php
namespace Perspective\Voting\Cron;

use Perspective\Voting\Model\ResourceModel\Voting\CollectionFactory;
use Perspective\Voting\Service\VoteCalculator;
use Perspective\Voting\Model\VotingOptionManager;
use Perspective\Voting\Service\ConfigData;
use Perspective\Voting\Model\Source\ManagementType;

class RefreshVotes
{
    protected $collectionFactory;
    protected $voteCalculator;
    protected $optionManager;
    protected $configDataService;

    public function __construct(
        CollectionFactory $collectionFactory,
        VoteCalculator $voteCalculator,
        VotingOptionManager $optionManager,
        ConfigData $configDataService
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->voteCalculator = $voteCalculator;
        $this->optionManager = $optionManager;
        $this->configDataService = $configDataService;
    }
    public function execute(): void
    {
        if (!$this->configDataService->isModuleEnabled()) {
            return;
        }

        $byDateIds = $this->collectionFactory->create()
            ->addFieldToFilter('is_finished', 0)
            ->addFieldToFilter('management_type', ManagementType::TYPE_BY_DATE)
            ->getAllIds();

        $manualActiveIds = $this->collectionFactory->create()
            ->addFieldToFilter('is_finished', 0)
            ->addFieldToFilter('management_type', ManagementType::TYPE_MANUAL)
            ->addFieldToFilter('status', 1)
            ->getAllIds();

        $allIds = array_unique(array_merge($byDateIds, $manualActiveIds));

        if (!empty($allIds)) {
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('voting_id', ['in' => $allIds]);

            foreach ($collection as $voting) {
                $votingId = (int)$voting->getId();
                $finalVotes = $this->voteCalculator->getFinalVotesByVotingId($votingId);
                $this->optionManager->updateVotes($finalVotes);
            }
        }
    }
}
