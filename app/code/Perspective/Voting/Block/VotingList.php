<?php
namespace Perspective\Voting\Block;

use Magento\Framework\View\Element\Template;
use Perspective\Voting\Service\UserIdentification;
use Perspective\Voting\Model\VoteManager;
use Perspective\Voting\Model\VotingManager;
use Magento\Framework\View\Element\Template\Context;

class VotingList extends Template
{
    /**
     * @var VotingManager
     */
    protected $votingManager;
    /**
     * @var VoteManager
     */
    protected $voteManager;
    /**
     * @var UserIdentification
     */
    protected $userIdentificationService;

    /**
     * @param Context $context
     * @param UserIdentification $userIdentificationService
     * @param VotingManager $votingManager
     * @param VoteManager $voteManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        UserIdentification $userIdentificationService,
        VotingManager $votingManager,
        VoteManager $voteManager,
        array $data = []
    ) {
        $this->votingManager = $votingManager;
        $this->voteManager = $voteManager;
        $this->userIdentificationService = $userIdentificationService;
        parent::__construct($context, $data);
    }

    /**
     * Get voting IDs based on block configuration parameters
     *
     * @return array
     */
    public function getVotingIds(): array
    {
        $ids = [];
        if ($this->getData('active_only')) {
            $ids = $this->votingManager->getActiveVotingIds();
        } elseif ($this->getData('customer_votes')) {
            $identityData = $this->userIdentificationService->getIdentityData();
            $customerId = $identityData['customer_id'];
            if ($customerId) {
                $ids = $this->voteManager->getVotingIdsByUserId($customerId);
            }
        }
        return $ids;
    }
}
