<?php

namespace Perspective\Memes\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Perspective\Memes\Model\Memes\MemeDataHandler;

class MemeConfigProvider implements ConfigProviderInterface
{
    protected $checkoutSession;
    protected $memeDataHandler;

    public function __construct(
        CheckoutSession $checkoutSession,
        MemeDataHandler $memeDataHandler
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->memeDataHandler = $memeDataHandler;
    }

    public function getConfig()
    {
        $quoteId = $this->checkoutSession->getQuote()->getEntityId();
        $memesData = $this->memeDataHandler->getMemes($quoteId, 'quote');

        return [
            'memesData' => $memesData
        ];
    }
}
