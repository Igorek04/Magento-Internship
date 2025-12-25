<?php
namespace Perspective\CartBonus\Model\Bonus;
use Perspective\CartBonus\Helper\Validation;
use Perspective\CartBonus\Helper\Data;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address\Total;
abstract class AbstractBonus
{

    public const BONUS_CODE = '';
    public const MESSAGE_TEMPLATE = '';
    public const BONUS_TOTAL_CODE = 'bonus_total';
    protected $validationHelper;
    protected $dataHelper;
    public function __construct(
        Validation $validationHelper,
        Data $dataHelper
    ) {
        $this->validationHelper = $validationHelper;
        $this->dataHelper = $dataHelper;
    }

    abstract public function isApplicable(CartInterface $quote, Total $total): bool;
    abstract public function apply(CartInterface $quote, Total $total): array;

    public function getCode(): string
    {
        return static::BONUS_CODE;
    }

    public function getConfig()
    {
        $code = $this->getCode();
        $a = $this->validationHelper->getBonusConfig($code);
        return $a;
    }

    public function isEnabled(): bool
    {
        return $this->validationHelper->isBonusEnabled($this->getCode());
    }

}
