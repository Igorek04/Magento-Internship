<?php
namespace Perspective\CustomerQuestions\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LikeValue implements OptionSourceInterface
{
    const VALUE_LIKE = 1;
    const VALUE_DISLIKE = -1;

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::VALUE_LIKE => __('Like'),
            self::VALUE_DISLIKE => __('Dislike')
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
