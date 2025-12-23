<?php
namespace Perspective\ProductReservation\Controller\Reservation;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Perspective\ProductReservation\Helper\DataValidation;
use Perspective\ProductReservation\Helper\Email;
use Perspective\ProductReservation\Model\Reservation\OrderModifier;
use Perspective\ProductReservation\Model\Reservation\OrderCreator;
use Perspective\ProductReservation\Model\Reservation\CustomerProvider;
use Throwable;

class Order extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var DataValidation
     */
    protected $dataValidator;
    /**
     * @var Email
     */
    protected $emailSender;
    /**
     * @var CustomerProvider
     */
    protected $customerProvider;
    /**
     * @var OrderCreator
     */
    protected $orderCreator;
    /**
     * @var OrderModifier
     */
    protected $orderModifier;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param DataValidation $dataValidator
     * @param Email $emailSender
     * @param CustomerProvider $customerProvider
     * @param OrderCreator $orderCreator
     * @param OrderModifier $orderModifier
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        DataValidation $dataValidator,
        Email $emailSender,
        CustomerProvider $customerProvider,
        OrderCreator $orderCreator,
        OrderModifier $orderModifier
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->dataValidator = $dataValidator;
        $this->emailSender = $emailSender;
        $this->customerProvider = $customerProvider;
        $this->orderCreator = $orderCreator;
        $this->orderModifier = $orderModifier;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        try {
            //get data
            $data = $this->getRequest()->getPostValue();
            $product = $this->productRepository->getById($data['product_id']);
            $telephone = $data['phone'];
            $store = $this->storeManager->getStore();

            //data validation
            $this->dataValidator->validateData($data, $telephone, $product);

            //get(create) customer
            $customer = $this->customerProvider->getCustomer($data, $store);

            //create order
            $order = $this->orderCreator->createOrder( $data, $telephone, $store, $product, $customer);

            //modify order(set reservation) and get order data($incrementId, $expiredAt)
            $orderData = $this->orderModifier->applyReservation($order);

            //send email
            $this->emailSender->sendCustomerReservationEmail($data['email'], $data['name'], $orderData['incrementId'], $orderData['expiredAt']);
            $this->emailSender->sendAdminReservationEmail($orderData['incrementId'], $orderData['expiredAt']);

            //store success msg
            $this->messageManager->addSuccessMessage(
                __('Product reserved successfully with order id #' . $orderData['incrementId'])
            );

            //test
            return $resultJson->setData([
                'success' => true,
                'received' => $data
            ]);

        } catch (Throwable $e) {
            //error msg in form
            return $resultJson->setData([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
