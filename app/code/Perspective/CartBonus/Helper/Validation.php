<?php
namespace Perspective\CartBonus\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
class Validation extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isModuleEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('cartbonus/general_settings/enabled');
    }

    public function isCartRulesApplied($quote): bool
    {
        $ids = $quote->getAppliedRuleIds();
        $items = $quote->getItems();

        return false;
    }
}
