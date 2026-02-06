<?php

namespace Perspective\Memes\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Perspective\Memes\Model\Memes\MemeManager;

class MemeConfigProvider implements ConfigProviderInterface
{
    protected $checkoutSession;
    protected $memeManager;

    public function __construct(
        CheckoutSession $checkoutSession,
        MemeManager $memeManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->memeManager = $memeManager;
    }

    public function getConfig()
    {
        $quoteId = $this->checkoutSession->getQuote()->getEntityId();
        $memesData = $this->memeManager->getData($quoteId, 'quote');

        return [
            'memesData' => $memesData
        ];
    }
}
