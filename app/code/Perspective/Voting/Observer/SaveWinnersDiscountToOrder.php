<?php

namespace Perspective\Voting\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Perspective\Voting\Service\ActiveWinners;
class SaveWinnersDiscountToOrder implements ObserverInterface
{
    protected $activeWinnersService;
    public function __construct(
        ActiveWinners $activeWinnersService
    ) {
        $this->activeWinnersService = $activeWinnersService;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        $discount = $this->activeWinnersService->getOrderWinnersDiscount($quote);
        $order->setData('winners_discount_amount', $discount);
        $test = 1;
    }
}
