<?php
namespace Perspective\Voting\Block\Sales\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Magento\Store\Model\Store;
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
    /**
     * @var ActiveWinners
     */
    protected $activeWinnersService;

    /**
     * @param Template\Context $context
     * @param ActiveWinners $activeWinnersService
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ActiveWinners $activeWinnersService,
        array $data = []
    ) {
        $this->activeWinnersService = $activeWinnersService;
        parent::__construct($context, $data);
    }

    /**
     * Add winners discount to order totals summary
     * (Used at admin/customer order view)
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();

        $value = $this->_order->getData('winners_discount_amount');
        if ($value != 0) {
            $this->_source = $parent->getSource();
            $total = new DataObject(
                [
                    'code'=>'perspective_voting_winners_discount_total',
                    'strong'=>false,
                    'value'=>$value,
                    'label'=>__('Winners Discount'),
                ]
            );
            $parent->addTotal($total, 'perspective_voting_winners_discount_total');
        }
        return $this;
    }

    /**
     * @return true
     */
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
    /**
     * @return Store
     */
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
}
