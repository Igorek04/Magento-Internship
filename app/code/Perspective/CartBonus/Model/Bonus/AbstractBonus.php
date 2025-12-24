<?php
namespace Perspective\CartBonus\Model\Bonus;
use Perspective\CartBonus\Helper\Validation;
use Perspective\CartBonus\Helper\Data;
abstract class AbstractBonus
{

    public const BONUS_CODE = '';
    protected $validationHelper;
    protected $dataHelper;
    public function __construct(
        Validation $validationHelper,
        Data $dataHelper
    ) {
        $this->validationHelper = $validationHelper;
        $this->dataHelper = $dataHelper;
    }

    abstract public function isApplicable($quote): bool;
    abstract public function apply($quote): array;

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
