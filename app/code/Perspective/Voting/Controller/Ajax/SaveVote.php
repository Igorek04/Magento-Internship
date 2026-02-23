<?php
namespace Perspective\Voting\Controller\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Customer\Model\Session;

class SaveVote implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var RequestInterface
     */
    protected $request;

    protected $session;


    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        Session $session
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        // get params from request
        $params = $this->request->getParams();

        $test = $this->session->getCustomer();


        $result = $this->resultJsonFactory->create();

        return $result->setData([
            'success' => true,
            'message' => 'Controller reached!',
            'received_data' => $params
        ]);

    }

}
