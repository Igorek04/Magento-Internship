<?php
namespace Perspective\Memes\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Perspective\Memes\Model\Memes\MemeManager;
use Magento\Backend\Model\Session\Quote as AdminQuoteSession;

class CopyMemesToAdminQuote implements ObserverInterface
{
    /**
     * @var MemeManager
     */
    protected $memeManager;
    /**
     * @var AdminQuoteSession
     */
    protected $adminQuoteSession;

    /**
     * @param MemeManager $memeManager
     * @param AdminQuoteSession $adminQuoteSession
     */
    public function __construct(
        MemeManager $memeManager,
        AdminQuoteSession $adminQuoteSession
    ) {
        $this->memeManager = $memeManager;
        $this->adminQuoteSession = $adminQuoteSession;
    }

    /**
     * Copy memes from parent order to new quote in admin
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        // get created quote
        $quote = $observer->getEvent()->getQuote();
        if (!$quote->getData('order_memes')) {
            $parentOrderId = $this->adminQuoteSession->getOrderId();
            $parentMemesData = $this->memeManager->getData($parentOrderId, 'order');

            // copy memes data into created quote
            $quote->setData('order_memes', json_encode($parentMemesData));
        }
    }
}
