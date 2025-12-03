<?php

namespace Perspective\OrderContractFile\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Contract extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Add download link for contract file in order grid
     *
     * @param array $dataSource
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as &$item) {
                $orderId = $item['entity_id'];
                $filePath = $this->orderRepository->get($orderId)->getData('contract_file');

                // if order has contract_file -> create link to download controller
                if ($filePath) {
                    $downloadUrl = $this->urlBuilder->getUrl(
                        'perspective_ordercontractfile/order_contract/download',
                        ['order_id' => $orderId]
                    );
                    // set HTML for link in grid cell
                    $item[$fieldName] = '<div style="text-align:center;">
                                            <a href="' . $downloadUrl . '" target="_blank" onclick="event.stopPropagation();">
                                                ⎙ Download
                                            </a>
                                        </div>';
                } else {
                    // show "Not Defined" if no contract file
                    $item[$fieldName] = '<div style="text-align:center;"><span style="color:red;">Not Defined</span></div>';
                }
            }
        }
        return $dataSource;
    }
}
