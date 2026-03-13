<?php
namespace Perspective\Voting\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\AlreadyExistsException;
use Perspective\Voting\Exception\VotingException;
use Perspective\Voting\Service\ConfigData;
use Perspective\Voting\Service\Guest\CookieManager;
use Perspective\Voting\Model\VotingManager;
use Perspective\Voting\Model\VoteManager;

class GuestVoteRedirectAfterRegister
{
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var CookieManager
     */
    protected $cookieManager;
    /**
     * @var ConfigData
     */
    protected $configDataService;
    /**
     * @var VotingManager
     */
    protected $votingManager;
    /**
     * @var VoteManager
     */
    protected $voteManager;

    /**
     * @param Session $customerSession
     * @param CookieManager $cookieManager
     * @param ConfigData $configDataService
     * @param VotingManager $votingManager
     * @param VoteManager $voteManager
     */
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

    /**
     * @param $subject
     * @param $result
     * @return mixed
     * @throws AlreadyExistsException
     * @throws VotingException
     */
    public function afterExecute($subject, $result)
    {
        //get temp guest vote(vote before redirect)
        $temporaryVote = $this->customerSession->getData('temporary_vote', true);
        if ($this->configDataService->isModuleEnabled() &&
            $this->customerSession->isLoggedIn()
        ) {
            //redirect to voting page
            $result->setUrl($this->customerSession->getData('voting_referer', true));

            //set temporary guest vote to new registered customer
            $guestHash = $this->cookieManager->getGuestCookie();
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
