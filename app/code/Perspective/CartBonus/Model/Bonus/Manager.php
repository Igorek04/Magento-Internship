<?php
namespace Perspective\CartBonus\Model\Bonus;
use Magento\Quote\Api\Data\CartInterface;
use Perspective\CartBonus\Helper\Validation;
class Manager
{
    protected $validationHelper;

    /** @var \Perspective\CartBonus\Model\Bonus\AbstractBonus[] */
    private array $bonuses;

    public function __construct(
        Validation $validationHelper,
        array $bonuses = []
    ) {
        $this->validationHelper = $validationHelper;
        $this->bonuses = $bonuses;
    }

    public function test (CartInterface $quote)
    {
        $data=[];
        if (!$this->validationHelper->isModuleEnabled() ||
            $this->validationHelper->isCartRulesApplied($quote)
        ) {
            return $data; // отрицательный ответ(или пустой?)
        }

        foreach ($this->bonuses as $bonus) {
            $testt = $bonus->getCode();
            $bonus->isApplicable($quote);
            $a = 1;
        }


    }
}
