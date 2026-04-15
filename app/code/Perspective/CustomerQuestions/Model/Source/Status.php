<?php
namespace Perspective\CustomerQuestions\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_REJECTED => __('Rejected')
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }
}
