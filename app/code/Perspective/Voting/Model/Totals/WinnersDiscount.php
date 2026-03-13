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
    public const TOTAL_TITLE = 'Winners Discount';
    public const TOTAL_CODE = 'perspective_voting_winners_discount_total';

    protected $totalDiscount = null;

    /**
     * @var ActiveWinners
     */
    protected $activeWinnersService;

    /**
     * @param ActiveWinners $activeWinnersService
     */
    public function __construct(
        ActiveWinners $activeWinnersService,
    ) {
        $this->activeWinnersService = $activeWinnersService;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this|WinnersDiscount
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $winnersDiscount = $this->getTotalDiscount($quote);

        $total->addTotalAmount(self::TOTAL_CODE, $winnersDiscount);
        $total->addBaseTotalAmount(self::TOTAL_CODE, $winnersDiscount);

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

    /**
     * @param Quote $quote
     * @return string
     */
    private function getTotalDiscount(Quote $quote): string
    {
        if ($this->totalDiscount === null) {
            $this->totalDiscount = $this->activeWinnersService->getOrderWinnersDiscount($quote);
        }
        return $this->totalDiscount;
    }
}
