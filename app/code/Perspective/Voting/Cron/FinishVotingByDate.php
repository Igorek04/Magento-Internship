<?php
namespace Perspective\Voting\Cron;

use Perspective\Voting\Model\VotingManager;
use Perspective\Voting\Service\ConfigData;
use Perspective\Voting\Model\ResourceModel\Voting\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Perspective\Voting\Model\Source\ManagementType;
use Psr\Log\LoggerInterface;


class FinishVotingByDate
{
    protected $votingManager;
    protected $configDataService;
    protected $votingCollectionFactory;
    protected $dateTime;
    protected $logger;
    public function __construct(
        VotingManager $votingManager,
        ConfigData $configDataService,
        CollectionFactory $votingCollectionFactory,
        DateTime $dateTime,
        LoggerInterface $logger,
    ) {
        $this->votingManager = $votingManager;
        $this->configDataService = $configDataService;
        $this->votingCollectionFactory = $votingCollectionFactory;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
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

        $finished = 0;
        foreach ($collection as $voting) {
            $votingId = $voting->getId();
            $this->votingManager->finishVoting($votingId);
            $finished++;
        }
        $this->logger->info('Finished ' . $finished . ' votings');
    }
}
