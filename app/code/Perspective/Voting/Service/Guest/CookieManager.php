<?php
namespace Perspective\Voting\Service\Guest;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Perspective\Voting\Service\ConfigData;

class CookieManager
{
    public const GUEST_COOKIE_NAME = 'voting_guest_hash';

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;
    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;
    /**
     * @var ConfigData
     */
    protected $configDataService;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param ConfigData $configDataService
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        ConfigData $configDataService,
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->configDataService = $configDataService;
    }

    /**
     * @return string|null
     */
    public function getGuestCookie()
    {
        return $this->cookieManager->getCookie(self::GUEST_COOKIE_NAME);
    }

    /**
     * @param $cookieData
     * @return void
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function setGuestCookie($cookieData)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($this->configDataService->getGuestCookieLifetime())
            ->setPath('/')
            ->setHttpOnly(true);

        $this->cookieManager->setPublicCookie(self::GUEST_COOKIE_NAME, $cookieData, $metadata);
    }

    /**
     * @return void
     * @throws InputException
     * @throws FailureToSendException
     */
    public function deleteGuestCookie()
    {
        $this->cookieManager->deleteCookie(self::GUEST_COOKIE_NAME);
    }
}
