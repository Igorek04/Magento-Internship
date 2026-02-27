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

class SaveVote implements HttpPostActionInterface
{
    protected $resultJsonFactory;
    protected $request;
    protected $voteManager;
    protected $userIdentificationService;
    protected $logger;

    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        VoteManager $voteManager,
        UserIdentification $userIdentificationService,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->voteManager = $voteManager;
        $this->userIdentificationService = $userIdentificationService;
        $this->logger = $logger;
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

            $message = $this->voteManager->processVote($params['voting_id'], $params['option_id'], $identity);

            return $result->setData([
                'success' => true,
                'message' => $message
            ]);
        } catch (VotingException $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => __('An error occurred while processing your vote.')
            ]);
        }
    }
}
