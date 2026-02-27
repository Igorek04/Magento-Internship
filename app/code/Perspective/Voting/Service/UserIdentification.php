<?php

namespace Perspective\Voting\Service;

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Perspective\Voting\Service\Guest\CookieManager;


class UserIdentification
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var RequestInterface
     */
    protected $request;
    protected $cookieManager;


    public function __construct(
        Session $customerSession,
        RequestInterface $request,
        CookieManager $cookieManager
    ) {
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->cookieManager = $cookieManager;
    }

    // get data
    public function getIdentityData(): array
    {
        $customerId = $this->customerSession->getCustomerId();

        if ($customerId) {
            return [
                'customer_id' => $customerId,
                'guest_hash' => null
            ];
        }

        $guestHash = $this->cookieManager->getGuestCookie();

        return [
            'customer_id' => null,
            'guest_hash'  => $guestHash
        ];
    }

    // get or create(hash cookie    ) data if not exist
    public function initIdentityData()
    {
        $data = $this->getIdentityData();
        if (!$data['customer_id'] && !$data['guest_hash']) {

            $newHash = bin2hex(random_bytes(16));
            $this->cookieManager->setGuestCookie($newHash);
            $data['guest_hash'] = $newHash;
        }
        return $data;
    }
}
