<?php

namespace Perspective\Memes\Controller\Ajax;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Perspective\Memes\Model\Memes\MemeManager;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GetMemeData implements HttpGetActionInterface
{
    protected $resultJsonFactory;
    protected $request;
    protected $memeManager;
    protected $maskedQuoteIdInterface;

    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        MemeManager $memeManager,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdInterface
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->memeManager = $memeManager;
        $this->maskedQuoteIdInterface = $maskedQuoteIdInterface;
    }


    public function execute()
    {
        $maskedQuoteId = $this->request->getParam('maskedQuoteId');
        $result = $this->resultJsonFactory->create();

        try {
            $quoteId = $this->maskedQuoteIdInterface->execute($maskedQuoteId);
            $data = $this->memeManager->getData($quoteId, 'quote');

            return $result->setData([
                'success' => true,
                'data' => $data
            ]);

        } catch (NoSuchEntityException $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
