<?php
namespace Perspective\CartBonus\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;
use Perspective\CartBonus\Helper\Validation;

class TotalsPlugin{
    protected $validationHelper;

    public function __construct(
        Validation $validationHelper
    ) {
        $this->validationHelper = $validationHelper;
    }

    public function afterCollect(
        TotalsCollector $subject,
        Quote\Address\Total $result,
        Quote $quote
    ){
        $a = 1;
        $isModuleEnabled = $this->validationHelper->isModuleEnabled();
        $test = $this->validationHelper->isCartRulesApplied($quote);
        return $result;

    }

}
