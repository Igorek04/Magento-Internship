<?php
namespace Perspective\CartBonus\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;

class TotalsPlugin{
    public function afterCollect(
        \Magento\Quote\Model\Quote\TotalsCollector $subject,
        \Magento\Quote\Model\Quote\Address\Total $result,
        \Magento\Quote\Model\Quote $quote
    ){
        $a = 1;

        return $result;

    }

}
