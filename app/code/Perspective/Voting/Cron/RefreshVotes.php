<?php
namespace Perspective\Voting\Cron;

use Perspective\Voting\Model\ResourceModel\Voting\CollectionFactory;
use Perspective\Voting\Service\VoteCalculator;
use Perspective\Voting\Model\VotingOptionManager;
use Perspective\Voting\Service\ConfigData;
use Perspective\Voting\Service\CacheManager;
use Psr\Log\LoggerInterface;
use Perspective\Voting\Model\VotingManager;

class RefreshVotes
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var VoteCalculator
     */
    protected $voteCalculator;
    /**
     * @var VotingOptionManager
     */
    protected $optionManager;
    /**
     * @var ConfigData
     */
    protected $configDataService;
    /**
     * @var CacheManager
     */
    protected $cacheManager;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var VotingManager
     */
    protected $votingManager;

    /**
     * @param CollectionFactory $collectionFactory
     * @param VoteCalculator $voteCalculator
     * @param VotingOptionManager $optionManager
     * @param ConfigData $configDataService
     * @param CacheManager $cacheManager
     * @param LoggerInterface $logger
     * @param VotingManager $votingManager
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        VoteCalculator $voteCalculator,
        VotingOptionManager $optionManager,
        ConfigData $configDataService,
        CacheManager $cacheManager,
        LoggerInterface $logger,
        VotingManager $votingManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->voteCalculator = $voteCalculator;
        $this->optionManager = $optionManager;
        $this->configDataService = $configDataService;
        $this->cacheManager = $cacheManager;
        $this->logger = $logger;
        $this->votingManager = $votingManager;
    }

    /**
     * Recalculate vote statistics and refresh cache for all active votings
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->configDataService->isModuleEnabled()) {
            return;
        }

        $allIds = $this->votingManager->getActiveVotingIds();

        if (!empty($allIds)) {
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('voting_id', ['in' => $allIds]);

            foreach ($collection as $voting) {
                $votingId = (int)$voting->getId();
                $finalVotes = $this->voteCalculator->getFinalVotesByVotingId($votingId);
                $this->optionManager->updateVotes($finalVotes);
                $this->cacheManager->deleteVotingCache($votingId);
            }
        }
        $this->logger->info(__('Votes refreshed for %1 votings', count($allIds)));
    }
}
