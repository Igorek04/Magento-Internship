<?php

namespace Perspective\Voting\Plugin;

use Magento\Customer\Model\Session;
use Perspective\Voting\Service\ConfigData;
use Perspective\Voting\Service\Guest\CookieManager;
use Perspective\Voting\Model\VotingManager;
use Perspective\Voting\Model\VoteManager;

class GuestVoteRedirectAfterRegister
{
    protected $customerSession;
    protected $cookieManager;
    protected $configDataService;
    protected $votingManager;
    protected $voteManager;
    public function __construct(
        Session $customerSession,
        CookieManager $cookieManager,
        ConfigData $configDataService,
        VotingManager $votingManager,
        VoteManager $voteManager
    ) {
        $this->customerSession = $customerSession;
        $this->cookieManager = $cookieManager;
        $this->configDataService = $configDataService;
        $this->votingManager = $votingManager;
        $this->voteManager = $voteManager;
    }

    public function afterExecute($subject, $result)
    {
        $temporaryVote = $this->customerSession->getData('temporary_vote', true);
        if ($this->configDataService->isModuleEnabled() &&
            $this->customerSession->isLoggedIn()
        ) {
            $result->setUrl($this->customerSession->getData('voting_referer', true));
            $guestHash = $this->cookieManager->getGuestCookie();

            //set temporary guest vote to new registered customer
            $customerId = $this->customerSession->getCustomer()->getId();
            $identity = [
                'customer_id' => $customerId,
                'guest_hash' => null
            ];
            $this->voteManager->processVote($temporaryVote['voting_id'], $temporaryVote['option_id'], $identity);

            $this->voteManager->convertGuestVotesToCustomer($guestHash, $customerId);
        }
        $this->cookieManager->deleteGuestCookie();
        return $result;
    }
}
