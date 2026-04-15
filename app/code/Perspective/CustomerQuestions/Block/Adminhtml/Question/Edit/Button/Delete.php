<?php
namespace Perspective\CustomerQuestions\Block\Adminhtml\Question\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Delete extends Generic implements ButtonProviderInterface
{
    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData(): array
    {
        $data = [];
        $voting_id = $this->context->getRequest()->getParam('entity_id');

        if ($voting_id) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl(): string
    {
        $entity_id = $this->context->getRequest()->getParam('entity_id');
        return $this->getUrl('*/*/delete', ['entity_id' => $entity_id]);
    }
}
