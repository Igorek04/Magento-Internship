<?php

namespace Perspective\Voting\Service;

use Perspective\Voting\Model\Voting;
use Perspective\Voting\Model\Source\ManagementType;
use Magento\Framework\Exception\LocalizedException;


class VotingValidation
{
    /**
     * @throws LocalizedException
     */
    public function validateSave(Voting $voting, $data): void
    {
        if ($this->isVotingFinished($voting)) {
            throw new LocalizedException(__('Voting already finished.'));
        }

        if (!$this->isManagementTypeManual($data)) {
            if (!$this->isEndDateFilled($data)) {
                throw new LocalizedException(__('Please set "End Date" for this management type.'));
            }

            if (!$this->isEndDateInFuture($data)) {
                throw new LocalizedException(__('The end date cannot be in the past.'));
            }
        }

        if (!$this->hasMinimumOptions($data)) {
            throw new LocalizedException(__('A voting must have at least 2 options.'));
        }
    }

    public function isVotingFinished(Voting $voting): bool
    {
        return (bool)$voting->getIsFinished();

    }

    public function isEndDateFilled($data): bool
    {
        return !empty($data['end_date']);
    }

    public function isManagementTypeManual($data): bool
    {
        return $data['management_type'] == ManagementType::TYPE_MANUAL;
    }

    public function isEndDateInFuture($data): bool
    {
        $currentTime = strtotime('now');
        $selectedTime = strtotime($data['end_date']);
        return $currentTime < $selectedTime;
    }

    public function hasMinimumOptions($data): bool
    {
        return !empty($data['data']['options_container']['options_container']) &&
                count($data['data']['options_container']['options_container']) >= 2;
    }
}
