<?php
namespace Perspective\Voting\Service;

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Perspective\Voting\Service\Guest\CookieManager;
use Random\RandomException;

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
    /**
     * @var CookieManager
     */
    protected $cookieManager;

    /**
     * @param Session $customerSession
     * @param RequestInterface $request
     * @param CookieManager $cookieManager
     */
    public function __construct(
        Session $customerSession,
        RequestInterface $request,
        CookieManager $cookieManager
    ) {
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->cookieManager = $cookieManager;
    }

    /**
     * Get user identity data from session or existing guest cookie
     *
     * @return array
     */
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

    /**
     * Get identity data or create a new guest hash if none exists
     *
     * @return array
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws RandomException
     */
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
