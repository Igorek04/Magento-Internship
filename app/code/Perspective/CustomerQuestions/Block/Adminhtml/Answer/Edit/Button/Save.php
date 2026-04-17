<?php
namespace Perspective\CustomerQuestions\Block\Adminhtml\Answer\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Perspective\CustomerQuestions\Block\Adminhtml\Question\Edit\Button\Generic;

class Save extends Generic implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return [
            'label' => __('Save Answer'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'customer_answers_form.areas',
                                'actionName' => 'save',
                                'params' => [true],
                            ],
                        ],
                    ],
                ],
            ],
            'sort_order' => 30,
        ];
    }
}
