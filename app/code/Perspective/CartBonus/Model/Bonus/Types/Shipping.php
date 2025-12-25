<?php
namespace Perspective\CartBonus\Model\Bonus\Types;
class Shipping extends \Perspective\CartBonus\Model\Bonus\AbstractBonus
{
    public const BONUS_CODE = "shipping";
    public const MESSAGE_TEMPLATE = 'Bonus: %d%% discount for shipping';
    public function isApplicable($quote, $total): bool
    {
        if (!$this->isEnabled()){
            return false;
        }
        $config = $this->getConfig();

        if ($config['first_threshold_min_total'] == null &&
            $config['first_threshold_discount_value'] == null &&
            $config['second_threshold_min_total'] == null &&
            $config['second_threshold_discount_value'] == null
        ) {
            return false;
        }

        $items = $quote->getItems();
        if (!$items) {
            return false;
        }




        if ($total->getBaseTotalAmount()){}





        return 1;
    }

    public function apply($quote, $total): array
    {
        return [1,1];
    }
}
