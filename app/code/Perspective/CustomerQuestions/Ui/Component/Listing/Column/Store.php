<?php
namespace Perspective\CustomerQuestions\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Store extends Column
{
    protected $storeManager;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                try {
                    $store = $this->storeManager->getStore($item['store_id']);
                    $item[$this->getData('name')] = $store->getName();
                } catch (\Exception $e) {
                    $item[$this->getData('name')] = __('Unknown Store');
                }
            }
        }
        return $dataSource;
    }
}
