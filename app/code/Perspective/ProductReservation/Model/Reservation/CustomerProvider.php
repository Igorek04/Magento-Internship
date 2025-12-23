<?php
namespace Perspective\ProductReservation\Model\Reservation;

use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerProvider
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerFactory;
    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerFactory
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerFactory,
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param $data
     * @param $store
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function getCustomer($data, $store)
    {
        $email = $data['email'];
        $websiteId = $store->getWebsiteId();
        try {
            return $this->customerRepository->get($email, $websiteId);
        } catch (NoSuchEntityException) {
            $customer = $this->customerFactory->create();
            $customer->setStoreId($store->getStoreId());
            $customer->setWebsiteId($websiteId);
            $customer->setEmail($email);
            $customer->setFirstname($data['name']);
            $customer->setLastname('Anonymous');
            $this->customerRepository->save($customer);
        }
        return $this->customerRepository->get($email, $websiteId);
    }
}
