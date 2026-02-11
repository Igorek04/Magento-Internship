<?php
namespace Perspective\Memes\Controller\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Perspective\Memes\Model\Memes\MemeManager;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class UpdateSelectedMeme implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var MemeManager
     */
    protected $memeManager;
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    protected $maskedQuoteIdInterface;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param MemeManager $memeManager
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        MemeManager $memeManager,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdInterface,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->memeManager = $memeManager;
        $this->maskedQuoteIdInterface = $maskedQuoteIdInterface;
        $this->logger = $logger;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        // get params from request
        $maskedQuoteId = $this->request->getParam('maskedQuoteId');
        $entityType = $this->request->getParam('entityType');
        $selected = $this->request->getParam('selected');

        $result = $this->resultJsonFactory->create();

        try {
            // get quote id
            if (ctype_digit($maskedQuoteId)) {
                $quoteId = $maskedQuoteId; // if loggedIn customer (quote id without mask)
            } else {
                $quoteId = $this->maskedQuoteIdInterface->execute($maskedQuoteId); // if guest (masked quote id)
            }

            // save updated data(selected) to quote
            $this->memeManager->updateSelected($quoteId, $entityType, $selected);

            return $result->setData([
                'success' => true,
                'selected' => $selected,
            ]);
        } catch (NoSuchEntityException $e) {
            $this->logger->error(__('Selected meme update failed. %1', $e->getMessage()));
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
