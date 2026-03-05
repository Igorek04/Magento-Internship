<?php

namespace Perspective\Voting\Model;

use Perspective\Voting\Model\Source\ManagementType;
use Perspective\Voting\Model\VotingFactory;
use Perspective\Voting\Model\ResourceModel\Voting as VotingResourceModel;
use Perspective\Voting\Service\VotingValidation;
use Perspective\Voting\Model\VotingOptionManager;
use Perspective\Voting\Service\VoteCalculator;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Perspective\Voting\Service\CacheManager;
use Perspective\Voting\Model\ResourceModel\Voting\CollectionFactory;



class VotingManager
{
    protected $votingResourceModel;
    protected $votingFactory;
    protected $votingValidationService;
    protected $votingOptionManager;
    protected $voteCalculatorService;
    protected $dateTime;
    protected $cacheManager;
    protected $collectionFactory;

    public function __construct(
        VotingResourceModel $votingResourceModel,
        VotingFactory       $votingFactory,
        VotingValidation    $votingValidationService,
        VotingOptionManager $votingOptionManager,
        VoteCalculator       $voteCalculatorService,
        DateTime           $dateTime,
        CacheManager        $cacheManager,
        CollectionFactory   $collectionFactory
    ) {
        $this->votingResourceModel = $votingResourceModel;
        $this->votingFactory = $votingFactory;
        $this->votingValidationService = $votingValidationService;
        $this->votingOptionManager = $votingOptionManager;
        $this->voteCalculatorService = $voteCalculatorService;
        $this->dateTime = $dateTime;
        $this->cacheManager = $cacheManager;
        $this->collectionFactory = $collectionFactory;
    }

    public function saveVotingData(array $data, $votingId = null): Voting
    {
        $model = $this->votingFactory->create();

        if ($votingId) {
            $this->votingResourceModel->load($model, $votingId);
        }

        //validation
        $this->votingValidationService->validateSave($model, $data);

        $model->setData($data);
        $this->votingResourceModel->save($model);

        return $model;
    }

    public function getById(int $id): Voting
    {
        $model = $this->votingFactory->create();
        $this->votingResourceModel->load($model, $id);
        return $model;
    }

    public function getActiveVotingIds(): array
    {
        $byDateIds = $this->collectionFactory->create()
            ->addFieldToFilter('is_finished', 0)
            ->addFieldToFilter('management_type', ManagementType::TYPE_BY_DATE)
            ->getAllIds();

        $manualActiveIds = $this->collectionFactory->create()
            ->addFieldToFilter('is_finished', 0)
            ->addFieldToFilter('management_type', ManagementType::TYPE_MANUAL)
            ->addFieldToFilter('status', 1)
            ->getAllIds();

        return array_unique(array_merge($byDateIds, $manualActiveIds));
    }

    public function finishVoting(int $votingId): void
    {
        $voting = $this->getById($votingId);

        $finalVotes = $this->voteCalculatorService->getFinalVotesByVotingId($votingId);

        if (!empty($finalVotes)) {
            $this->votingOptionManager->updateVotes($finalVotes);

            $winnerOptionId = key($finalVotes);
            $voting->setWinnerOptionId($winnerOptionId);
        }

        $voting->setIsFinished(1);
        $voting->setFinishedAt($this->dateTime->gmtDate());

        $this->votingResourceModel->save($voting);

        $this->cacheManager->deleteVotingCache($votingId);
        $this->cacheManager->deleteWinnersCache();
    }

    public function isGuestVotingAllowed($votingId): bool
    {
        $voting = $this->getById($votingId);
        return $voting->getAllowGuests();
    }
}
