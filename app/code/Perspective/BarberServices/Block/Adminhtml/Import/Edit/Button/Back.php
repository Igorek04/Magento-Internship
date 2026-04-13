<?php
namespace Perspective\BarberServices\Block\Adminhtml\Import\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
class Back extends Generic implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('adminhtml/dashboard')),
            'class' => 'back',
            'sort_order' => 10,
        ];
    }
}
