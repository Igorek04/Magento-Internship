<?php
namespace Perspective\Voting\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ManagementType implements OptionSourceInterface
{
    public const TYPE_MANUAL = 0;
    public const TYPE_BY_DATE = 1;

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::TYPE_MANUAL, 'label' => __('Manual')],
            ['value' => self::TYPE_BY_DATE, 'label' => __('By Date')]
        ];
    }
}
