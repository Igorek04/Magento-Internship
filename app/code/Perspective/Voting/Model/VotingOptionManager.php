<?php

namespace Perspective\Voting\Model;

use Perspective\Voting\Model\VotingOptionFactory as OptionFactory;
use Perspective\Voting\Model\ResourceModel\VotingOption as OptionResourceModel;
use Perspective\Voting\Model\ResourceModel\VotingOption\CollectionFactory as OptionCollectionFactory;

class VotingOptionManager
{
    protected $optionFactory;
    protected $optionResourceModel;
    protected $optionCollectionFactory;

    public function __construct(
        OptionFactory $optionFactory,
        OptionResourceModel $optionResourceModel,
        OptionCollectionFactory $optionCollectionFactory
    ) {
        $this->optionFactory = $optionFactory;
        $this->optionResourceModel = $optionResourceModel;
        $this->optionCollectionFactory = $optionCollectionFactory;
    }


    public function saveVotingOptions(int $votingId, array $optionsData): void
    {
        $processedOptionIds = [];
        foreach ($optionsData as $option) {
            $optionModel = $this->optionFactory->create();

            // if edit option
            if (!empty($option['option_id'])) {

                $this->optionResourceModel->load($optionModel, $option['option_id']);
                $processedOptionIds[] = (int)$option['option_id'];
            }

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

    public function getById(int $id): VotingOption
    {
        $optionModel = $this->optionFactory->create();
        $this->optionResourceModel->load($optionModel, $id);
        return $optionModel;
    }

    public function getOptionsByVotingId(int $votingId)
    {
        $options = $this->optionCollectionFactory->create();
        $options->addFieldToFilter('voting_id', $votingId);
        return $options;

    }

    public function updateVotes(array $results): void
    {
        $collection = $this->optionCollectionFactory->create();
        $collection->addFieldToFilter('option_id', ['in' => array_keys($results)]);
        foreach ($collection as $option) {
            $optionId = $option->getId();
            $option->setTotalVotes($results[$optionId]);
            $this->optionResourceModel->save($option);
        }
    }
}
