<?php
namespace Perspective\CartBonus\Model\Bonus\Types;
class Shipping extends \Perspective\CartBonus\Model\Bonus\AbstractBonus
{
    public const BONUS_CODE = "shipping";
    public function isApplicable($quote): bool
    {
        $config = $this->getConfig();
        return 1;
    }

    public function apply($quote): array
    {
        return [1,1];
    }
}
