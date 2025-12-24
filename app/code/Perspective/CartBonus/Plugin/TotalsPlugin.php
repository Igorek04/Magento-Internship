<?php
namespace Perspective\CartBonus\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;
use Perspective\CartBonus\Helper\Validation;
use Perspective\CartBonus\Model\Bonus\Manager;

class TotalsPlugin{
    protected $validationHelper;
    protected $bonusManager;

    public function __construct(
        Validation $validationHelper,
        Manager $bonusManager
    ) {
        $this->validationHelper = $validationHelper;
        $this->bonusManager = $bonusManager;
    }

    public function afterCollect(
        TotalsCollector $subject,
        Quote\Address\Total $result,
        Quote $quote
    ){
        $a = 1;
        $isModuleEnabled = $this->validationHelper->isModuleEnabled();
        $isCartRulesApplied = $this->validationHelper->isCartRulesApplied($quote);



        return $result;
    }
}
