<?php

namespace Perspective\Memes\Controller\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Perspective\Memes\Model\Memes\MemeManager;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class UpdateSelectedMeme implements HttpPostActionInterface
{
    protected $resultJsonFactory;
    protected $request;
    protected $memeManager;
    protected $maskedQuoteIdInterface;
    protected $logger;

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

    public function execute()
    {
        $maskedQuoteId = $this->request->getParam('maskedQuoteId');
        $entityType = $this->request->getParam('entityType');
        $selected = $this->request->getParam('selected');

        $result = $this->resultJsonFactory->create();

        try {
            if (ctype_digit($maskedQuoteId)) {
                $quoteId = $maskedQuoteId; // if loggedIn customer (quote id without mask)
            } else {
                $quoteId = $this->maskedQuoteIdInterface->execute($maskedQuoteId); // if guest (masked quote id)
            }

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
