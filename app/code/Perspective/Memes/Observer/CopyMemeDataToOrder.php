<?php

namespace Perspective\Memes\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Perspective\Memes\Model\Memes\MemeManager;


class CopyMemeDataToOrder implements ObserverInterface
{
    protected $memeManager;
    public function __construct(
        MemeManager $memeManager
    ) {
        $this->memeManager = $memeManager;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        // if order
        $quoteId = $order->getQuoteId();

        //$parentOrderId = $order->getData('relation_parent_id');
        //if ($parentOrderId) { // if order have parent id - get data from parent order ( for admin memes edit saving)
            //$memesData = $this->memeManager->getData($parentOrderId, 'order');
        //} else {
            $memesData = $this->memeManager->getData($quoteId, 'quote');
        //}



        $order->setData('order_memes', json_encode($memesData));
        $test = 1;
    }
}
