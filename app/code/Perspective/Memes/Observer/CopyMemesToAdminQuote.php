<?php
namespace Perspective\Memes\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Perspective\Memes\Model\Memes\MemeManager;
use Magento\Backend\Model\Session\Quote as AdminQuoteSession;

class CopyMemesToAdminQuote implements ObserverInterface
{
    protected $memeManager;
    protected $adminQuoteSession;

    public function __construct(
        MemeManager $memeManager,
        AdminQuoteSession $adminQuoteSession
    ) {
        $this->memeManager = $memeManager;
        $this->adminQuoteSession = $adminQuoteSession;
    }

    public function execute(Observer $observer)
    {
        // fill memes data to new quote from parent(edited) order
        $quote = $observer->getEvent()->getQuote();
        if (!$quote->getData('order_memes')) {
            $parentOrderId = $this->adminQuoteSession->getOrderId();
            $parentMemesData = $this->memeManager->getData($parentOrderId, 'order');

            $quote->setData('order_memes', json_encode($parentMemesData));
        }
    }
}
