<?php

namespace Perspective\Voting\Model;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
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
    /**
     * @var VotingResourceModel
     */
    protected $votingResourceModel;
    /**
     * @var VotingFactory
     */
    protected $votingFactory;
    /**
     * @var VotingValidation
     */
    protected $votingValidationService;
    /**
     * @var VotingOptionManager
     */
    protected $votingOptionManager;
    /**
     * @var VoteCalculator
     */
    protected $voteCalculatorService;
    /**
     * @var DateTime
     */
    protected $dateTime;
    /**
     * @var CacheManager
     */
    protected $cacheManager;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param VotingResourceModel $votingResourceModel
     * @param VotingFactory $votingFactory
     * @param VotingValidation $votingValidationService
     * @param VotingOptionManager $votingOptionManager
     * @param VoteCalculator $voteCalculatorService
     * @param DateTime $dateTime
     * @param CacheManager $cacheManager
     * @param CollectionFactory $collectionFactory
     */
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

    /**
     * Create/load voting and save data
     *
     * @param array $data
     * @param $votingId
     * @return Voting
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
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

    /**
     * Get voting object by id
     *
     * @param int $id
     * @return Voting
     */
    public function getById(int $id): Voting
    {
        $model = $this->votingFactory->create();
        $this->votingResourceModel->load($model, $id);
        return $model;
    }

    /**
     * Get all active voting ids(manual active + by date)
     *
     * @return array
     */
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

    /**
     * Finish and save vote
     *
     * @param int $votingId
     * @return void
     * @throws AlreadyExistsException
     */
    public function finishVoting(int $votingId): void
    {
        $voting = $this->getById($votingId);

        //update votes before finish voting
        $finalVotes = $this->voteCalculatorService->getFinalVotesByVotingId($votingId);
        if (!empty($finalVotes)) {
            $this->votingOptionManager->updateVotes($finalVotes);

            //set winner option
            $winnerOptionId = key($finalVotes);
            $voting->setWinnerOptionId($winnerOptionId);
        }

        //finish status and timestamp
        $voting->setIsFinished(1);
        $voting->setFinishedAt($this->dateTime->gmtDate());

        $this->votingResourceModel->save($voting);

        //clear cache
        $this->cacheManager->deleteVotingCache($votingId);
        $this->cacheManager->deleteWinnersCache();
    }

    /**
     * Check if guests can vote for this voting
     *
     * @param $votingId
     * @return bool
     */
    public function isGuestVotingAllowed($votingId): bool
    {
        $voting = $this->getById($votingId);
        return $voting->getAllowGuests();
    }
}
