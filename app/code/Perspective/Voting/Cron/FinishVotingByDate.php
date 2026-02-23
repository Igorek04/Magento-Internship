<?php
namespace Perspective\Voting\Cron;

use Perspective\Voting\Model\VotingManager;
use Perspective\Voting\Service\ConfigData;
use Perspective\Voting\Model\ResourceModel\Voting\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Perspective\Voting\Model\Source\ManagementType;
use Psr\Log\LoggerInterface;
use Perspective\Voting\Service\CacheManager;


class FinishVotingByDate
{
    protected $votingManager;
    protected $configDataService;
    protected $votingCollectionFactory;
    protected $dateTime;
    protected $logger;
    protected $cacheManager;
    public function __construct(
        VotingManager $votingManager,
        ConfigData $configDataService,
        CollectionFactory $votingCollectionFactory,
        DateTime $dateTime,
        LoggerInterface $logger,
        CacheManager $cacheManager
    ) {
        $this->votingManager = $votingManager;
        $this->configDataService = $configDataService;
        $this->votingCollectionFactory = $votingCollectionFactory;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->cacheManager = $cacheManager;
    }

    public function execute(): void
    {
        if (!$this->configDataService->isModuleEnabled()) {
            return;
        }
        $now = $this->dateTime->gmtDate();
        $collection = $this->votingCollectionFactory->create();
        $collection->addFieldToFilter('is_finished', 0)
            ->addFieldToFilter('management_type', ManagementType::TYPE_BY_DATE)
            ->addFieldToFilter('end_date', ['lteq' => $now]);

        foreach ($collection as $voting) {
            $votingId = $voting->getId();
            $this->votingManager->finishVoting($votingId);
            $this->cacheManager->deleteVotingCache($votingId);
        }
    }
}
