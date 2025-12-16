<?php

namespace Perspective\ProductReservation\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Api\CustomerRepositoryInterface;



class FormData implements ArgumentInterface
{
    private ?bool $isLoggedIn = null;
    private $customer = null;

    protected $customerSession;
    protected $customerRepository;
    public function __construct(
        CustomerSession $customerSession,
        customerRepositoryInterface $customerRepository
    ){
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    public function isCustomerLoggedIn(){
        if ($this->isLoggedIn === null) {
            $this->isLoggedIn = $this->customerSession->isLoggedIn();
        }
        return $this->isLoggedIn;
    }

    public function getCustomer(){
        return $this->customerRepository->getById($this->customerSession->getCustomerId());
    }

    public function getCustomerFirstName(){
        if ($this->isCustomerLoggedIn()){
            return $this->getCustomer()->getFirstname();
        }
        return null;
    }

    public function getCustomerEmail(){
        if ($this->isCustomerLoggedIn()){
            return $this->getCustomer()->getEmail();
        }
        return null;
    }

    public function getCustomerPhone(){
        if ($this->isCustomerLoggedIn()){
            return $this->getCustomer()->get();//через адрес репо
        }
        return null;
    }

}
