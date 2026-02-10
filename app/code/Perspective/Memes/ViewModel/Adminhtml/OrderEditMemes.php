<?php

namespace Perspective\Memes\ViewModel\Adminhtml;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Perspective\Memes\Model\Memes\MemeManager;
use Magento\Backend\Model\Session\Quote;

class OrderEditMemes implements ArgumentInterface
{
    protected $memeManager;
    protected $adminQuoteSession;

    public function __construct(
        MemeManager $memeManager,
        Quote $adminQuoteSession,
    ) {
        $this->memeManager = $memeManager;
        $this->adminQuoteSession = $adminQuoteSession;
    }


    public function getJsonMemesData(): string
    {
        $entityId = $this->getQuoteId();
        return json_encode($this->memeManager->getData($entityId, 'quote'));
    }

    public function getQuoteId()
    {
        return $this->adminQuoteSession->getQuoteId();
    }
}
