<?php
namespace Perspective\Voting\Block\Adminhtml\Index\Edit\Button;

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
        $voting_id = $this->context->getRequest()->getParam('voting_id');

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
        $voting_id = $this->context->getRequest()->getParam('voting_id');
        return $this->getUrl('*/*/delete', ['voting_id' => $voting_id]);
    }
}
