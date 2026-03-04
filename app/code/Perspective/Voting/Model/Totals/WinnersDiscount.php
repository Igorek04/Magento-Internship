<?php

namespace Perspective\Voting\Model\Totals;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Framework\Phrase;
use Perspective\Voting\Service\ActiveWinners;


class WinnersDiscount extends AbstractTotal
{
    public const TOTAL_TITLE = 'Winners Discount Total';
    public const TOTAL_CODE = 'perspective_voting_winners_discount_total';

    protected $totalDiscount = null;

    protected $activeWinnersService;

    public function __construct(
        ActiveWinners $activeWinnersService,
    ) {
        $this->activeWinnersService = $activeWinnersService;
    }

    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        //parent::collect($quote, $shippingAssignment, $total); ?? мб дублирование фиксил
        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $insurancePrice = $this->getTotalDiscount($quote);

        $total->addTotalAmount(self::TOTAL_CODE, $insurancePrice);
        $total->addBaseTotalAmount(self::TOTAL_CODE, $insurancePrice);

        return $this;
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total): array
    {
        return [
            'code' => $this->getCode(),
            'title' => $this->getLabel(),
            'value' => $this->getTotalDiscount($quote),
        ];
    }

    /**
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __(self::TOTAL_TITLE);
    }

    private function getTotalDiscount(Quote $quote): string
    {
        if ($this->totalDiscount === null) {
            $this->totalDiscount = $this->activeWinnersService->getOrderWinnersDiscount($quote);
        }
        return $this->totalDiscount;
    }
}
