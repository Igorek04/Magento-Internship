<?php

namespace Perspective\Voting\Service\Guest;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Perspective\Voting\Service\ConfigData;

class CookieManager
{
    public const GUEST_COOKIE_NAME = 'voting_guest_hash';
    protected $cookieManager;
    protected $cookieMetadataFactory;
    protected $configDataService;
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        ConfigData $configDataService,
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->configDataService = $configDataService;
    }

    public function getGuestCookie()
    {
        return $this->cookieManager->getCookie(self::GUEST_COOKIE_NAME);
    }

    public function setGuestCookie($cookieData)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($this->configDataService->getGuestCookieLifetime())
            ->setPath('/')
            ->setHttpOnly(true);

        $this->cookieManager->setPublicCookie(self::GUEST_COOKIE_NAME, $cookieData, $metadata);
    }
}
