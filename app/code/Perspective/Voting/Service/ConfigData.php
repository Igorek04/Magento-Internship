<?php
namespace Perspective\Voting\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigData
{
    private const XML_PATH_ENABLED = 'perspective_voting/general_settings/enabled';
    private const XML_PATH_ADMIN_EDIT_VOTES = 'perspective_voting/general_settings/allow_admin_edit_votes';
    private const XML_PATH_SHOW_DISCOUNT_LABEL = 'perspective_voting/general_settings/product_discount_label';
    private const XML_PATH_SHOW_DISCOUNT_DURATION = 'perspective_voting/general_settings/product_discount_duration';
    private const XML_PATH_GUEST_COOKIE_LIFETIME = 'perspective_voting/general_settings/guest_cookie_ttl';


    protected $scopeConfig;
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag($this::XML_PATH_ENABLED);
    }

    /**
     * @return bool
     */
    public function isAdminAllowedEditVotes(): bool
    {
        return $this->scopeConfig->isSetFlag($this::XML_PATH_ADMIN_EDIT_VOTES);
    }

    /**
     * @return bool
     */
    public function isShowDiscountLabel(): bool
    {
        return $this->scopeConfig->isSetFlag($this::XML_PATH_SHOW_DISCOUNT_LABEL);
    }

    /**
     * @return int
     */
    public function getDiscountDuration(): int
    {
        return (int)$this->scopeConfig->getValue($this::XML_PATH_SHOW_DISCOUNT_DURATION);
    }

    public function getGuestCookieLifetime(): int
    {
        return (int)$this->scopeConfig->getValue($this::XML_PATH_GUEST_COOKIE_LIFETIME) * 86400;
    }
}
