<?php
namespace Perspective\Voting\Service;

use Perspective\Voting\Model\Source\ManagementType;
use Perspective\Voting\Model\VotingManager;

class VotingState
{
    public const STATE_FINISHED = 'finished';
    public const STATE_AUTO     = 'auto';
    public const STATE_ACTIVE   = 'active';

    protected $votingManager;
    public function __construct(
        VotingManager $votingManager
    ) {
        $this->votingManager = $votingManager;
    }

    public function getStateByVotingId(int $id): string
    {
        $voting = $this->votingManager->getById($id);

        return match (true) {
            (bool)$voting->getIsFinished() => self::STATE_FINISHED,
            $voting->getManagementType() == ManagementType::TYPE_BY_DATE => self::STATE_AUTO,
            default => self::STATE_ACTIVE
        };
    }
}
