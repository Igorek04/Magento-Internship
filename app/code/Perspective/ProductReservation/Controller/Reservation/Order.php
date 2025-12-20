<?php

namespace Perspective\ProductReservation\Controller\Reservation;

use DateTime;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderRepository;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Perspective\ProductReservation\Helper\DataValidation;
use Perspective\ProductReservation\Helper\Email;
use Throwable;
use Magento\InventoryApi\Api\SourceRepositoryInterface;


class Order extends Action
{
    const PICKUP_SOURCE = 'instore_source'; //custom source for instore_pickup shipping(also need custom stock)
    const SHIPPING_METHOD = 'instore_pickup'; // instore pickup потрібно налаштувати в мадженті вручну(кастом source підключений до кастом stock), завантажені геодані
    const PAYMENT_METHOD = 'cashondelivery'; 

    protected $resultJsonFactory;
    protected $context;
    protected $_storeManager;
    protected $productRepository;
    protected $quote;
    protected $quoteManagement;
    protected $orderService;
    protected $customerFactory;
    protected $customerRepository;
    protected $quoteRepository;
    protected $shippingConfig;
    protected $orderRepository;
    protected $timezone;
    protected $dataValidator;
    protected $emailSender;
    protected $sourceRepository;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param QuoteFactory $quote
     * @param QuoteManagement $quoteManagement
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param Config $shippingConfig
     * @param OrderRepository $orderRepository
     * @param TimezoneInterface $timezone
     * @param DataValidation $dataValidator
     * @param Email $emailSender
     */
    public function __construct(
        Context                     $context,
        JsonFactory                 $resultJsonFactory,
        StoreManagerInterface       $storeManager,
        QuoteFactory                $quote,
        QuoteManagement             $quoteManagement,
        CustomerInterfaceFactory    $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface  $productRepository,
        CartRepositoryInterface     $quoteRepository,
        Config                      $shippingConfig,
        OrderRepository             $orderRepository,
        TimezoneInterface           $timezone,
        DataValidation              $dataValidator,
        Email                       $emailSender,
        SourceRepositoryInterface $sourceRepository
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_storeManager = $storeManager;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->quoteRepository = $quoteRepository;
        $this->shippingConfig = $shippingConfig;
        $this->orderRepository = $orderRepository;
        $this->timezone = $timezone;
        $this->dataValidator = $dataValidator;
        $this->emailSender = $emailSender;
        $this->sourceRepository = $sourceRepository;
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

            //data validations
            $this->dataValidator->validateName($data['name']);
            $this->dataValidator->validateEmail($data['email']);
            $this->dataValidator->validatePhone($telephone);
            $this->dataValidator->validateProduct($product);

            //create quote
            $store = $this->_storeManager->getStore();
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $customer = $this->getCustomer($data, $websiteId, $store);
            $quote = $this->quote->create();
            $quote->setStore($store);
            //$quote->setCurrency();
            $quote->assignCustomer($customer);
            $quote->addProduct($product, intval($data['qty'])); // продукт що має qty в кастомному source

            //customer address data with placeholders
            $source = $this->sourceRepository->get(self::PICKUP_SOURCE); 
            $sourceAddressData = $this->getAddressDataFromSource($source);
            $billingAddress = $quote->getBillingAddress()->addData(
                $sourceAddressData + [
                'telephone' => $telephone,
                'firstname' => $data['name'],
                'lastname' => $customer->getLastname(),
            ]);
            //source address
            $shippingAddress = $quote->getShippingAddress()->addData(
                $sourceAddressData + [
                'telephone' => $source->getPhone(),
                'firstname' => 'admin',
                'lastname' => 'adminovich',   
            ]);
            $shippingAddress->getExtensionAttributes()->setPickupLocationCode(self::PICKUP_SOURCE); 

            $shippingAddress->setCollectShippingRates(true)
                            ->collectShippingRates()
                            ->setShippingMethod(self::SHIPPING_METHOD) 
                            ->setPaymentMethod(self::PAYMENT_METHOD);

            $quote->setPaymentMethod(self::PAYMENT_METHOD);
            $quote->setInventoryProcessed(false);
            $this->quoteRepository->save($quote);
            $quote->getPayment()->importData(array('method' => self::PAYMENT_METHOD));
            $quote->collectTotals();

            //потрібно для валідації тут: vendor/magento/module-inventory-in-store-pickup-quote/Model/Quote/ValidationRule/InStorePickupQuoteValidationRule.php
            $shippingAddress->setSameAsBilling(false)
                            ->setSaveInAddressBook(false)
                            ->setCustomerAddressId(null);

            //save quote into order
            $this->quoteRepository->save($quote);
            $order = $this->quoteManagement->submit($quote);
            $quote = $customer = null;

            //order modifications
            $increment_id = $order->getIncrementId(); 
                //date for comment
            $createdAt = $order->getCreatedAt();
            $modifiedDate = $this->timezone->date(new DateTime($createdAt))->modify('+1 day')->format('Y-m-d H:i');
                //order reservation attribute, status, state, comment
            $order->setData('is_reservation', 1);
            $order->setState('reservation');
            $order->setStatus('reservation');
            $order->addCommentToStatusHistory('Reserved until ' . $modifiedDate);
            $this->orderRepository->save($order);
                //store success msg
            $this->messageManager->addSuccessMessage(
                __('Product reserved successfully with order id #' . $increment_id)
            );

            //customer email
            $this->emailSender->send(
                'reservation_add_customer_email',
                $data['email'],
                [
                    'name' => $data['name'],
                    'increment_id' => $increment_id,
                    'expired_at' => $modifiedDate
                ]
            );
            //admin email
            $this->emailSender->send(
                'reservation_add_admin_email',
                'admin_mail',
                [
                    'increment_id' => $increment_id,
                    'expired_at' => $modifiedDate
                ]
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

    /**
     * @param $data
     * @param $websiteId
     * @param $store
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws NoSuchEntityException
     */
    private function getCustomer($data, $websiteId, $store)
    {
        $email = $data['email'];
        $name = $data['name'];
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

    private function getAddressDataFromSource($source)
    {
        return [
            'country_id' => $source->getCountryId(),
            'region_id'  => $source->getRegionId(),
            'region'     => $source->getRegion(),
            'city'       => $source->getCity(),
            'street'     => $source->getStreet(),
            'postcode'   => $source->getPostcode()
        ];
    }
}
