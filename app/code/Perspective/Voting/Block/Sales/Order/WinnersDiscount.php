<?php

namespace Perspective\Voting\Block\Sales\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Magento\Tax\Block\Sales\Order\Tax;
use Perspective\Voting\Model\Totals\WinnersDiscount as TotalModel;
use Perspective\Voting\Service\ActiveWinners;

class WinnersDiscount extends Template
{
    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var DataObject
     */
    protected $_source;

    protected $activeWinnersService;


    public function __construct(
        Template\Context $context,
        ActiveWinners $activeWinnersService,
        array $data = []
    ) {
        $this->activeWinnersService = $activeWinnersService;
        parent::__construct($context, $data);
    }

    public function displayFullSummary()
    {
        return true;
    }
    /**
     * @return DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }
    public function getStore()
    {
        return $this->_order->getStore();
    }
    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }
    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }
    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();

        $value = $this->_order->getData('winners_discount_amount');

        $this->_source = $parent->getSource();
        $store = $this->getStore();
        $total = new DataObject(
            [
                'code'=>'perspective_voting_winners_discount_total',
                'strong'=>false,
                'value'=>$value,
                'label'=>__('perspective_voting_winners_discount_total'),
            ]
        );
        $parent->addTotal($total, 'perspective_voting_winners_discount_total');
        return $this;
    }
}
