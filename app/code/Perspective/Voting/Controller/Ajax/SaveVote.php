<?php
namespace Perspective\Voting\Controller\Ajax;

use Exception;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Perspective\Voting\Model\VoteManager;
use Perspective\Voting\Service\UserIdentification;
use Psr\Log\LoggerInterface;
use Perspective\Voting\Exception\VotingException;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Perspective\Voting\Model\VotingManager;

class SaveVote implements HttpPostActionInterface
{
    protected $resultJsonFactory;
    protected $request;
    protected $voteManager;
    protected $userIdentificationService;
    protected $logger;
    protected $urlInterface;
    protected $customerSession;
    protected $votingManager;

    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        VoteManager $voteManager,
        UserIdentification $userIdentificationService,
        LoggerInterface $logger,
        UrlInterface $urlInterface,
        CustomerSession $customerSession,
        VotingManager $votingManager
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->voteManager = $voteManager;
        $this->userIdentificationService = $userIdentificationService;
        $this->logger = $logger;
        $this->urlInterface = $urlInterface;
        $this->customerSession = $customerSession;
        $this->votingManager = $votingManager;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $params = $this->request->getParams();

            $identity = $this->userIdentificationService->getIdentityData();

            //if no customer id + guest not allow
            if (!$identity['customer_id'] && !$this->votingManager->isGuestVotingAllowed($params['voting_id'])) {
                $refererUrl = $this->request->getServer('HTTP_REFERER');
                $this->customerSession->setData('voting_referer', $refererUrl);
                $this->customerSession->setData('temporary_vote', [
                    'voting_id' => $params['voting_id'],
                    'option_id' => $params['option_id']
                ]);

                return $result->setData([
                    'success'  => false,
                    'redirect' => true,
                    'url'      => $this->urlInterface->getUrl('customer/account/create')
                ]);
            }



            $message = $this->voteManager->processVote($params['voting_id'], $params['option_id'], $identity);

            return $result->setData([
                'success' => true,
                'redirect' => false,
                'message' => $message
            ]);
        } catch (VotingException $e) {
            return $result->setData([
                'success' => false,
                'redirect' => false,
                'message' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return $result->setData([
                'success' => false,
                'redirect' => false,
                'message' => __('An error occurred while processing your vote.')
            ]);
        }
    }
}
