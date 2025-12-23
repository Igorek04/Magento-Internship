<?php

namespace Perspective\ProductReservation\Model\Reservation;

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

class OrderCreator
{
    const PICKUP_SOURCE = 'source_pickup'; //custom source for instore_pickup shipping(also need custom stock)
    const SHIPPING_METHOD = 'instore_pickup'; //instore pickup потрібно налаштувати в мадженті вручну(кастом source підключений до кастом stock), завантажені геодані
    const PAYMENT_METHOD = 'cashondelivery';
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;
    /**
     * @var SourceRepositoryInterface
     */
    protected $sourceRepository;

    /**
     * @param QuoteFactory $quoteFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param SourceRepositoryInterface $sourceRepository
     * @param QuoteManagement $quoteManagement
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        CartRepositoryInterface $quoteRepository,
        SourceRepositoryInterface $sourceRepository,
        QuoteManagement $quoteManagement,
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->sourceRepository = $sourceRepository;
        $this->quoteManagement = $quoteManagement;
    }

    /**
     * @param array $data
     * @param string $telephone
     * @param $store
     * @param $product
     * @param $customer
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createOrder(array $data, string $telephone, $store, $product, $customer)
    {
        $quote = $this->quoteFactory->create()
                                    ->setStore($store)
                                    ->assignCustomer($customer);
        $quote->addProduct($product, intval($data['qty'])); // продукт що має qty в кастомному source

        //get source data
        $source = $this->sourceRepository->get(self::PICKUP_SOURCE);
        $sourceAddressData = $this->getAddressDataFromSource($source);

        //set customer address data(source placeholders)
        $quote->getBillingAddress()->addData(
            $sourceAddressData + [
                'telephone' => $telephone,
                'firstname' => $data['name'],
                'lastname' => $customer->getLastname()
            ]);

        //set shipping(source) address data
        $shippingAddress = $quote->getShippingAddress()->addData(
            $sourceAddressData + [
                'telephone' => $source->getPhone(),
                'firstname' => 'admin',
                'lastname' => 'adminovich'
            ]);
        $shippingAddress->getExtensionAttributes()->setPickupLocationCode(self::PICKUP_SOURCE);

        $shippingAddress->setCollectShippingRates(true) //qty reservation
                        ->collectShippingRates()
                        ->setShippingMethod(self::SHIPPING_METHOD)
                        ->setPaymentMethod(self::PAYMENT_METHOD);

        $quote->setPaymentMethod(self::PAYMENT_METHOD)
                ->setInventoryProcessed(false);
        $this->quoteRepository->save($quote);
        $quote->getPayment()->importData(array('method' => self::PAYMENT_METHOD));
        $quote->collectTotals();

        //потрібно для валідації тут: vendor/magento/module-inventory-in-store-pickup-quote/Model/Quote/ValidationRule/InStorePickupQuoteValidationRule.php
        $shippingAddress->setSameAsBilling(false)
                        ->setSaveInAddressBook(false)
                        ->setCustomerAddressId(null);

        //save quote into order
        $this->quoteRepository->save($quote);
        return $this->quoteManagement->submit($quote);
    }

    /**
     * @param $source
     * @return array
     */
    private function getAddressDataFromSource($source): array
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
