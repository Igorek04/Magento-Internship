<?php
namespace Perspective\Memes\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Perspective\Memes\Model\Memes\MemeManager;

class MemeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @var MemeManager
     */
    protected $memeManager;

    /**
     * @param CheckoutSession $checkoutSession
     * @param MemeManager $memeManager
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        MemeManager $memeManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->memeManager = $memeManager;
    }

    /**
     * Provides meme data for the current quote to the frontend checkout
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        $quoteId = $this->checkoutSession->getQuote()->getEntityId();
        $memesData = $this->memeManager->getData($quoteId, 'quote');

        return [
            'memesData' => $memesData
        ];
    }
}
