<?php
namespace Perspective\Memes\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Perspective\Memes\Model\Memes\MemeManager;

class CopyMemeDataToOrder implements ObserverInterface
{
    /**
     * @var MemeManager
     */
    protected $memeManager;

    /**
     * @param MemeManager $memeManager
     */
    public function __construct(
        MemeManager $memeManager
    ) {
        $this->memeManager = $memeManager;
    }

    /**
     * Copy memes data from quote to order when order is placed
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        $quoteId = $order->getQuoteId();

        $memesData = $this->memeManager->getData($quoteId, 'quote');
        $order->setData('order_memes', json_encode($memesData));
    }
}
