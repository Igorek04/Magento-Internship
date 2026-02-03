<?php

namespace Perspective\Memes\Controller\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Perspective\Memes\Api\GiphyApi;

class Test implements HttpPostActionInterface
{
    protected $resultJsonFactory;
    protected $request;
    protected $giphyApiService;

    public function __construct(

        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        GiphyApi $giphyApiService
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->giphyApiService = $giphyApiService;
    }


    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $postData = $this->request->getParams();

        $test = $this->giphyApiService->request();

        return $result->setData([
            'success' => true,
            'message' => 'POST success',
            'postData' => $postData
        ]);
    }
}
