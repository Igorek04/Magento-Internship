<?php
namespace Perspective\CustomerQuestions\Ui\Component\Listing\Column;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Product extends Column
{
    protected $productRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProductRepositoryInterface $productRepository,
        array $components = [],
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                try {
                    $product = $this->productRepository->getById($item['product_id']);
                    $item[$this->getData('name')] = $product->getName();
                } catch (\Exception $e) {
                    $item[$this->getData('name')] = __('N/A (ID: %1)', $item['product_id']);
                }
            }
        }
        return $dataSource;
    }
}
