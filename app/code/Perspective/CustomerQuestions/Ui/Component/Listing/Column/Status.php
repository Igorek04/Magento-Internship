<?php
namespace Perspective\CustomerQuestions\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Perspective\CustomerQuestions\Model\Source\Status as StatusSource;

class Status extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $status = (int)$item['status'];

                $item[$this->getData('name')] = match ($status) {
                    StatusSource::STATUS_APPROVED => sprintf(
                        '<span style="color: green;">%s</span>',
                        __('Approved')
                    ),
                    StatusSource::STATUS_PENDING => sprintf(
                        '<span style="color: orange;">%s</span>',
                        __('Pending')
                    ),
                    StatusSource::STATUS_REJECTED => sprintf(
                        '<span style="color: red;">%s</span>',
                        __('Rejected')
                    ),
                    default => __('Unknown')
                };
            }
        }
        return $dataSource;
    }
}
