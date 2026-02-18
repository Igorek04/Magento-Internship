<?php
/**
 * @package Perspective_Voting
 */
declare(strict_types=1);

namespace Perspective\Voting\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for Voting management types
 */
class ManagementType implements OptionSourceInterface
{
    /**
     * Get options for Management Type
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 0,
                'label' => __('Manual Toggle')
            ],
            [
                'value' => 1,
                'label' => __('By End Date')
            ]
        ];
    }
}
