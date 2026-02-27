<?php

namespace Perspective\Voting\Service;

use Perspective\Voting\Model\Voting;
use Perspective\Voting\Model\Source\ManagementType;
use Magento\Framework\Exception\LocalizedException;
use Perspective\Voting\Exception\VotingException;


class VotingValidation
{
    /**
     * @throws LocalizedException
     */
    public function validateSave(Voting $voting, $data): void
    {
        if ($this->isVotingFinished($voting)) {
            throw new VotingException(__('Voting already finished.'));
        }

        if (!$this->isManagementTypeManual($data)) {
            if (!$this->isEndDateFilled($data)) {
                throw new VotingException(__('Please set "End Date" for this management type.'));
            }

            if (!$this->isEndDateInFuture($data)) {
                throw new VotingException(__('The end date cannot be in the past.'));
            }
        }

        if (!$this->hasMinimumOptions($data)) {
            throw new VotingException(__('A voting must have at least 2 options.'));
        }
    }

    public function canVote(Voting $voting, array $identity): void
    {
        if (empty($identity['customer_id']) && empty($identity['guest_hash'])) {
            throw new VotingException(__('Identification failed.'));
        }

        if ($this->isVotingFinished($voting)) {
            throw new VotingException(__('Voting already finished.'));
        }

        if ($this->isManagementTypeManual($voting)) {
            if (!$this->isManualStatusActive($voting)) {
                throw new VotingException(__('Voting is temporarily disabled.'));
            }
        } else {
            if (!$this->isEndDateInFuture($voting)) {
                throw new VotingException(__('The voting period has expired.'));
            }
        }
    }

    public function isVotingFinished(Voting $voting): bool
    {
        return (bool)$voting->getIsFinished();

    }

    public function isEndDateFilled($source): bool
    {
        if ($source instanceof Voting) {
            $endDate = $source->getEndDate();
        } else {
            // if post data for save
            $endDate = $source['end_date'];
        }
        return !empty($endDate);
    }

    public function isManagementTypeManual($source): bool
    {
        if ($source instanceof Voting) {
            $manageType = $source->getManagementType();
        } else {
            $manageType = $source['management_type'];
        }
        return $manageType == ManagementType::TYPE_MANUAL;
    }

    public function isEndDateInFuture($source): bool
    {
        if ($source instanceof Voting) {
            $endDate = $source->getEndDate();
        } else {
            $endDate = $source['end_date'];
        }

        $currentTime = strtotime('now');
        $selectedTime = strtotime($endDate);
        return $currentTime < $selectedTime;
    }

    public function hasMinimumOptions($data): bool
    {
        return !empty($data['data']['options_container']['options_container']) &&
                count($data['data']['options_container']['options_container']) >= 2;
    }

    public function isManualStatusActive(Voting $voting): bool
    {
        return $voting->getStatus();
    }
}
