<?php

namespace Perspective\ProductReservation\ViewModel;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel to fetch customer data for pre-filling the form when the user is logged in
 */
class FormData implements ArgumentInterface
{
    private ?bool $isLoggedIn = null;
    private $customer = null;

    /**
     * @var CustomerSession
     */
    protected $customerSession;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @param CustomerSession $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        CustomerSession $customerSession,
        customerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
    ){
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @return bool|null
     */
    public function isCustomerLoggedIn(){
        if ($this->isLoggedIn === null) {
            $this->isLoggedIn = $this->customerSession->isLoggedIn();
        }
        return $this->isLoggedIn;
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomer(){
        if (!$this->isCustomerLoggedIn()) {
            return null;
        }
        if ($this->customer === null) {
            $this->customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
        }
        return $this->customer;
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerFirstName(){
        if ($this->isCustomerLoggedIn()){
            return $this->getCustomer()->getFirstname();
        }
        return null;
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerEmail(){
        if ($this->isCustomerLoggedIn()){
            return $this->getCustomer()->getEmail();
        }
        return null;
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerPhone(){
        if ($this->isCustomerLoggedIn()){
            try {
                $billingAddressId = $this->getCustomer()->getDefaultBilling();
                return $this->addressRepository->getById($billingAddressId)->getTelephone();
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return null;
            }
        }
        return null;
    }
}
