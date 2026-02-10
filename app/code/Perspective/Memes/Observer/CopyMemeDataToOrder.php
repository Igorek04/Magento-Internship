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
        $quoteId = $order->getQuoteId();

        $memesData = $this->memeManager->getData($quoteId, 'quote');
        $order->setData('order_memes', json_encode($memesData));
    }
}
