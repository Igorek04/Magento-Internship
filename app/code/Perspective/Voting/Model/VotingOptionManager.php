<?php
namespace Perspective\Voting\Model;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Perspective\Voting\Model\VotingOptionFactory as OptionFactory;
use Perspective\Voting\Model\ResourceModel\VotingOption as OptionResourceModel;
use Perspective\Voting\Model\ResourceModel\VotingOption\CollectionFactory as OptionCollectionFactory;

class VotingOptionManager
{
    /**
     * @var VotingOptionFactory
     */
    protected $optionFactory;
    /**
     * @var OptionResourceModel
     */
    protected $optionResourceModel;
    /**
     * @var OptionCollectionFactory
     */
    protected $optionCollectionFactory;

    /**
     * @param VotingOptionFactory $optionFactory
     * @param OptionResourceModel $optionResourceModel
     * @param OptionCollectionFactory $optionCollectionFactory
     */
    public function __construct(
        OptionFactory $optionFactory,
        OptionResourceModel $optionResourceModel,
        OptionCollectionFactory $optionCollectionFactory,
    ) {
        $this->optionFactory = $optionFactory;
        $this->optionResourceModel = $optionResourceModel;
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    /**
     * Save/update voting options and remove deleted
     *
     * @param int $votingId
     * @param array $optionsData
     * @return void
     * @throws AlreadyExistsException
     */
    public function saveVotingOptions(int $votingId, array $optionsData): void
    {
        $processedOptionIds = [];
        foreach ($optionsData as $option) {
            $optionModel = $this->optionFactory->create();

            if (!empty($option['option_id'])) {
                //load existing option if editing
                $this->optionResourceModel->load($optionModel, $option['option_id']);
                $processedOptionIds[] = (int)$option['option_id'];
            }

            //prevent manual save vote statistic
            unset($option['total_votes']);


            $optionModel->setData($option);
            $optionModel->setVotingId($votingId);
            $this->optionResourceModel->save($optionModel);

            if (empty($option['option_id'])) {
                $processedOptionIds[] = (int)$optionModel->getId();
            }
        }

        $this->deleteRemovedOptions($votingId, $processedOptionIds);
    }

    /**
     * Delete options from voting that were removed in the admin panel
     *
     * @param int $votingId
     * @param array $processedOptionIds
     * @return void
     * @throws Exception
     */
    protected function deleteRemovedOptions(int $votingId, array $processedOptionIds): void
    {
        $optionCollection = $this->optionCollectionFactory->create();
        $optionCollection->addFieldToFilter('voting_id', $votingId);

        if (!empty($processedOptionIds)) {
            $optionCollection->addFieldToFilter('option_id', ['nin' => $processedOptionIds]);
        }

        foreach ($optionCollection as $optionToDelete) {
            $this->optionResourceModel->delete($optionToDelete);
        }
    }

    /**
     * Get option object by option id
     *
     * @param int $id
     * @return VotingOption
     */
    public function getById(int $id): VotingOption
    {
        $optionModel = $this->optionFactory->create();
        $this->optionResourceModel->load($optionModel, $id);
        return $optionModel;
    }

    /**
     * Get option list by voting id
     *
     * @param int $votingId
     * @return OptionResourceModel\Collection
     */
    public function getOptionsByVotingId(int $votingId)
    {
        $options = $this->optionCollectionFactory->create();
        $options->addFieldToFilter('voting_id', $votingId);
        return $options;

    }

    /**
     * Update total votes for options using the provided results array
     *
     * @param array $results [option_id => total_votes]
     * @return void
     * @throws AlreadyExistsException
     */
    public function updateVotes(array $results): void
    {
        //get all options that need a vote count update
        $collection = $this->optionCollectionFactory->create();
        $collection->addFieldToFilter('option_id', ['in' => array_keys($results)]);
        foreach ($collection as $option) {
            $optionId = $option->getId();
            $option->setTotalVotes($results[$optionId]);
            $this->optionResourceModel->save($option);
        }
    }
}
