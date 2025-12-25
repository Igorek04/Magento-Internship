<?php
namespace Perspective\CartBonus\Model\Totals;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;
use Perspective\CartBonus\Model\Bonus\Manager;

class Bonus extends AbstractTotal
{
    protected $bonusManager;

    public function __construct(
        Manager $bonusManager,
    )
    {
        $this->bonusManager = $bonusManager;
        $this->setCode('bonus_total');
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }
        $frontendData = $this->bonusManager->test($quote, $total);
        $total->setData('bonus_frontend_data', $frontendData);

        return $this;
    }

    /**
     * @param Total $total
     */
    protected function clearValues(Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total)
    {
        $frontendData = $total->getData('bonus_frontend_data');
        if ($frontendData == null) {
            $frontendData = [
                'bonus_discount' => 0,
                'bonus_messages' => []
            ];
        }

        return [
            'code' => $this->getCode(),
            'title' => 'Bonus Total',
            'value' => -$frontendData['bonus_discount'],
            //'messages' => $frontendData['bonus_messages'],
            //'extension_attributes' => [$frontendData['bonus_messages']] //проблема с получением\отправкой данных на фронт
        ];
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Bonus Total');
    }
}
