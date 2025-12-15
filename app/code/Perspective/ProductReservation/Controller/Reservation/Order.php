<?php
namespace Perspective\ProductReservation\Controller\Reservation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;


class Order extends Action
{
    protected $resultJsonFactory;
    protected $context;
    protected $_storeManager;
    protected $productRepository;
    protected $_formkey;
    protected $quote;
    protected $quoteManagement;
    protected $orderService;
    protected $customerFactory;
    protected $customerRepository;
    protected $quoteRepository;
    protected $shippingConfig;
    protected $orderRepository;
    protected $timezone;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface $quoteRepository,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_storeManager = $storeManager;
        $this->_formkey = $formkey;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->quoteRepository = $quoteRepository;
        $this->shippingConfig = $shippingConfig;
        $this->orderRepository = $orderRepository;
        $this->timezone = $timezone;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {
            $data = $this->getRequest()->getPostValue();

            $productId = $data['product_id'];
            if ($this->productRepository->getById($productId)->getTypeId() == 'configurable') {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __('Please select product options')
                ]);
            }
            //data validation   helper?

            $store = $this->_storeManager->getStore();
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $customer = $this->getCustomer($data, $websiteId, $store);
            $product = $this->productRepository->getById($productId);

            $quote=$this->quote->create();
            $quote->setStore($store);
            $quote->setCurrency();
            $quote->assignCustomer($customer);
            $quote->addProduct($product, intval(array('qty'   => $data['qty'])));

            $telephone = $data['phone'];
            if ($telephone === "") {
                $telephone = '0';
            }

            $billingAddress = $quote->getBillingAddress()->addData(array(
                'telephone' => $telephone,
                'country_id' => 'US',
                'firstname' => $data['name'],
                'lastname' => $customer->getLastname(),
                'region_id' => 1,
                'street' => 'a',
                'city' => 'New York',
                'postcode' => '07008',
            ));
            $shippingAddress = $quote->getShippingAddress()->addData(array(
                'country_id' => 'US',
                'firstname' => 'admin',
                'lastname' => 'adminovich',
                'region_id' => 1,
                'street' => 'a',
                'city' => 'New York',
                'telephone' => '1234567890',
                'postcode' => '07008',
            ));
            $shippingAddress->setCollectShippingRates(true)
                            ->collectShippingRates()
                            ->setShippingMethod('flatrate_flatrate') //ПРОБЛЕМА С НАСТРОЙКОЙ МЕТОДА INSTORE
                            ->setPaymentMethod('cashondelivery');



            /*
             //test
            $availableMethods = [];
            foreach ($shippingAddress->getAllShippingRates() as $rate) {
                $availableMethods[] = [
                    'code'  => $rate->getCode(),
                    'title' => $rate->getMethodTitle(),
                ];
            }
            $carriers = $this->shippingConfig->getActiveCarriers();
            */


            $quote->setPaymentMethod('cashondelivery');
            $quote->setInventoryProcessed(false);
            $this->quoteRepository->save($quote);
            $quote->getPayment()->importData(array('method' => 'cashondelivery'));
            $quote->collectTotals();
            $this->quoteRepository->save($quote);
            $service = $this->quoteManagement->submit($quote);


            $increment_id = $service->getRealOrderId();
            $quote = $customer = $service = null;
            $order = $this->orderRepository->get($increment_id);


            $createdAt = $order->getCreatedAt();
            $modifiedDate = $this->timezone->date(new \DateTime($createdAt))->modify('+1 day')->format('Y-m-d H:i');


            $order->setData('is_reservation', 1);
            $order->addCommentToStatusHistory('Reserved until ' . $modifiedDate);
            $this->orderRepository->save($order);

            //success msg
            $this->messageManager->addSuccessMessage(
                __('Product reserved successfully with order id %1', $increment_id)
            );

            return $resultJson->setData([
                'success' => true,
                'received' => $data
            ]);

        } catch (\Throwable $e) {
            return $resultJson->setData([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => $e->getMessage(),
            ]);
        }
    }



    public function getCustomer($data, $websiteId, $store)
    {
        $email = $data['email'] ;
        $name = $data['name'] ;
        try {
            return $this->customerRepository->get($email, $websiteId);
        } catch (NoSuchEntityException $e) {
            $customer = $this->customerFactory->create();
            $customer->setStoreId($store->getStoreId());
            $customer->setWebsiteId($store->getWebsiteId());
            $customer->setEmail($email);
            $customer->setFirstname($name);
            $customer->setLastname('Anonymous');
            $this->customerRepository->save($customer);
        }
        return $this->customerRepository->get($email, $websiteId);
    }
}
