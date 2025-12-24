<?php
namespace Perspective\CartBonus\Model\Bonus\Types;
class Gift extends \Perspective\CartBonus\Model\Bonus\AbstractBonus
{
    public const BONUS_CODE = 'gift';
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
