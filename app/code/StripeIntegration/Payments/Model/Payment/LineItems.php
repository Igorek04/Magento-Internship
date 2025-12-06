<?php

declare(strict_types=1);

namespace StripeIntegration\Payments\Model\Payment;

use StripeIntegration\Payments\Exception\Exception;

class LineItems
{
    private $order = null;
    private array $lineItems = [];

    private $helper;
    private $config;
    private $configHelper;
    private $invoiceItemHelper;
    private $loggerHelper;
    private $hasLineItemTaxes = false;
    private $hasLineItemDiscounts = false;

    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\Logger $loggerHelper,
        \StripeIntegration\Payments\Helper\Config $configHelper,
        \StripeIntegration\Payments\Helper\Stripe\InvoiceItem $invoiceItemHelper,
        \StripeIntegration\Payments\Model\Config $config
    )
    {
        $this->helper = $helper;
        $this->invoiceItemHelper = $invoiceItemHelper;
        $this->config = $config;
        $this->loggerHelper = $loggerHelper;
        $this->configHelper = $configHelper;
    }

    public function fromOrder($order)
    {
        try
        {
            $this->order = $order;
            $this->addLineItems();
        }
        catch (\Exception $e)
        {
            $this->order = null;
            $this->loggerHelper->logError("LineItems generation error for order " . $order->getIncrementId() . ": " . $e->getMessage());
        }

        return $this;
    }

    public function addItem(
        string $productCode,
        string $productName,
        int $unitCost,
        int $quantity,
        string $unitOfMeasure,
        ?int $taxAmount,
        ?int $discountAmount,
        array $paymentMethodOptions
    ): void
    {
        $lineItem = [
            'product_code' => $productCode,
            'product_name' => $productName,
            'unit_cost' => $unitCost,
            'quantity' => $quantity,
            'unit_of_measure' => $unitOfMeasure
        ];

        if (is_numeric($taxAmount))
        {
            $lineItem['tax']['total_tax_amount'] = $taxAmount;
            $this->hasLineItemTaxes = true;
        }

        if (is_numeric($discountAmount) && $discountAmount > 0)
        {
            $lineItem['discount_amount'] = $discountAmount;
            $this->hasLineItemDiscounts = true;
        }

        if (!empty($paymentMethodOptions))
        {
            $lineItem['payment_method_options'] = $paymentMethodOptions;
        }

        $this->lineItems[] = $lineItem;
    }

    private function addLineItems(): void
    {
        $order = $this->order;
        $this->hasLineItemTaxes = false;
        $this->hasLineItemDiscounts = false;

        foreach ($order->getAllItems() as $orderItem)
        {
            if (!$this->invoiceItemHelper->shouldIncludeOnInvoice($orderItem))
            {
                continue;
            }

            // Get product details
            $productCode = $this->getProductCode($orderItem);
            $productName = $this->getProductName($orderItem);
            $unitCost = $this->getUnitCost($orderItem);
            $quantity = (int) $orderItem->getQtyOrdered();
            $taxAmount = $this->getTaxAmount($orderItem);
            $discountAmount = $this->getDiscountAmount($orderItem);
            $unitOfMeasure = $this->getUnitOfMeasure($orderItem);
            $paymentMethodOptions = $this->getPaymentMethodOptions($orderItem);

            $this->addItem(
                $productCode,
                $productName,
                $unitCost,
                $quantity,
                $unitOfMeasure,
                $taxAmount,
                $discountAmount,
                $paymentMethodOptions
            );
        }
    }

    public function getProductCode($orderItem): string
    {
        $sku = $orderItem->getSku();

        if (!empty($sku) && strlen($sku) > 12)
        {
            return $orderItem->getProductId();
        }

        return $sku;
    }

    public function getTaxAmount($orderItem): ?int
    {
        $order = $this->order;

        if (is_numeric($orderItem->getTaxAmount()) && $orderItem->getTaxAmount() > 0)
        {
            return $this->helper->convertMagentoAmountToStripeAmount(
                $orderItem->getTaxAmount(),
                $order->getOrderCurrencyCode()
            );
        }

        return null;
    }

    public function getDiscountAmount($orderItem): int
    {
        $order = $this->order;

        if (is_numeric($orderItem->getDiscountAmount()))
        {
            return abs($this->helper->convertMagentoAmountToStripeAmount(
                $orderItem->getDiscountAmount(),
                $order->getOrderCurrencyCode()
            ));
        }

        return 0;
    }

    // Unit cost is always tax exclusive. Taxes are added separately.
    public function getUnitCost($orderItem): int
    {
        $order = $this->order;

        return $this->helper->convertMagentoAmountToStripeAmount(
            $orderItem->getPrice(),
            $order->getOrderCurrencyCode()
        );
    }

    /**
     * More codes at https://service.unece.org/trade/uncefact/vocabulary/rec20/
     *
     * | Unit of Measure | Description                              |
     * | --------------- | ---------------------------------------- |
     * | `EA`            | Each (individual item)                   |
     * | `BOX`           | Box (grouped items in a box)             |
     * | `PKG`           | Package                                  |
     * | `HUR`           | Hour (for labor or time-based services)  |
     * | `DAY`           | Day (for rentals or lodging)             |
     * | `LB`            | Pound (weight-based measure)             |
     * | `KG`            | Kilogram                                 |
     * | `L`             | Liter                                    |
     * | `GAL`           | Gallon                                   |
     * | `FT`            | Foot (length)                            |
     * | `IN`            | Inch                                     |
     * | `M`             | Meter                                    |
     * | `SQFT`          | Square Foot (area-based)                 |
     * | `C62`           | Piece (ISO standard for countable items) |
     * | `SET`           | Set (bundle of items)                    |
     * | `SERV`          | Service (intangible unit of work)        |
     * | --------------- | ---------------------------------------- |
     */
    public function getUnitOfMeasure($orderItem): string
    {
        $product = $orderItem->getProduct();
        if ($product && $product->getData('unit_of_measure'))
        {
            return substr((string)$product->getData('unit_of_measure'), 0, 12);
        }

        return 'EA';
    }

    // See https://docs.stripe.com/payments/payment-line-items#additional-klarna-supported-fields
    public function getPaymentMethodOptions($orderItem): array
    {
        $options = [];

        return $options;
    }

    public function getProductName($orderItem): string
    {
        $name = $orderItem->getName();

        if ($orderItem->getParentItem() && $orderItem->getParentItem()->getProductType() == "bundle")
        {
            $name = $orderItem->getParentItem()->getName() . " - " . $name;
        }
        else if ($orderItem->getProductType() == "configurable")
        {
            $selections = [];
            $attributes = $orderItem->getProductOptionByCode('attributes_info');
            if ($attributes)
            {
                foreach ($attributes as $attribute)
                {
                    if (isset($attribute['value']))
                    {
                        $selections[] = $attribute['value'];
                    }
                }
            }

            if (count($selections) > 0)
            {
                $name = $name . " - " . implode(", ", $selections);
            }
        }

        // Truncate to Stripe's limit (1024 chars, but different payment methods have different limits)
        return substr($name, 0, 1024);
    }

    public function getShippingDetails(): array
    {
        $order = $this->order;

        if ($order->getIsVirtual())
        {
            return [];
        }

        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress)
        {
            return [];
        }

        $shippingAmount = $this->hasLineItemTaxes ? $order->getShippingInclTax() : $order->getShippingAmount();
        $shippingAmount = $this->helper->convertMagentoAmountToStripeAmount($shippingAmount, $order->getOrderCurrencyCode());

        return [
            'amount' => $shippingAmount,
            'from_postal_code' => $this->getFromPostalCode(),
            'to_postal_code' => $this->sanitizePostalCode($shippingAddress->getPostcode())
        ];
    }

    public function getFromPostalCode(): string
    {
        // Get store's postal code or use a default
        $store = $this->order->getStore();
        $storePostcode = $this->configHelper->getConfigData('shipping/origin/postcode', $store);
        return $this->sanitizePostalCode($storePostcode ?: '00000');
    }

    private function sanitizePostalCode($postcode): string
    {
        // Stripe allows max 10 chars, alphanumeric and hyphens
        $sanitized = preg_replace('/[^a-zA-Z0-9-]/', '', (string)$postcode);
        return substr($sanitized, 0, 10);
    }

    public function getPaymentIntentFormat(): array
    {
        $data = [
            'amount_details' => $this->getAmountDetails(),
            'payment_details' => $this->getPaymentDetails()
        ];

        if (!$this->isOrderGrandTotalEqualToLineItemsTotal($data))
        {
            $this->loggerHelper->logError("LineItems total does not match order grand total for order " . $this->order->getIncrementId() . ". Not sending line items to Stripe.");
            return [];
        }

        return $data;
    }

    public function getOrderReference(): string
    {
        return $this->order->getIncrementId();
    }

    public function getTotalTaxAmount(): int
    {
        $order = $this->order;
        $totalTaxAmount = 0;

        // Calculate total tax amount
        if (is_numeric($order->getTaxAmount()) && $order->getTaxAmount() > 0)
        {
            $totalTaxAmount = $this->helper->convertMagentoAmountToStripeAmount(
                $order->getTaxAmount(),
                $order->getOrderCurrencyCode()
            );
        }

        return $totalTaxAmount;
    }

    public function getTotalDiscountAmount(): ?int
    {
        $order = $this->order;
        $totalDiscountAmount = null;

        // Calculate total discount amount
        if (is_numeric($order->getDiscountAmount()))
        {
            $totalDiscountAmount = abs($this->helper->convertMagentoAmountToStripeAmount(
                $order->getDiscountAmount(),
                $order->getOrderCurrencyCode()
            ));
        }

        return $totalDiscountAmount;
    }

    public function getCustomerReference(): ?string
    {
        $order = $this->order;

        if ($order->getCustomerId())
        {
            return 'customer_' . $order->getCustomerId();
        }

        return null;
    }

    public function getAmountDetails(): array
    {
        $amountDetails = [];

        // Add line items if available
        if (!empty($this->lineItems)) {
            $lineItems = [];

            foreach ($this->lineItems as $item) {
                $lineItem = [
                    'product_code' => $item['product_code'],
                    'product_name' => $item['product_name'],
                    'unit_cost' => $item['unit_cost'],
                    'quantity' => $item['quantity'],
                    'unit_of_measure' => $item['unit_of_measure']
                ];

                if (isset($item['tax']['total_tax_amount']) && $item['tax']['total_tax_amount'] > 0) {
                    $lineItem['tax'] = [
                        'total_tax_amount' => $item['tax']['total_tax_amount']
                    ];
                }

                if (isset($item['discount_amount']) && $item['discount_amount'] > 0) {
                    $lineItem['discount_amount'] = $item['discount_amount'];
                }

                if (isset($item['payment_method_options']) && !empty($item['payment_method_options'])) {
                    $lineItem['payment_method_options'] = $item['payment_method_options'];
                }

                $lineItems[] = $lineItem;
            }

            if (count($lineItems) > 0) {
                $amountDetails['line_items'] = $lineItems;
            }
        }

        // Add tax amount if no line item taxes exist
        if (!$this->hasLineItemTaxes) {
            $totalTaxAmount = $this->getTotalTaxAmount();
            if ($totalTaxAmount > 0) {
                $amountDetails['tax']['total_tax_amount'] = $totalTaxAmount;
            }
        }

        // Add shipping details if order is not virtual
        $shipping = $this->getShippingDetails();
        if (!empty($shipping)) {
            $amountDetails['shipping'] = $shipping;
        }

        // Add discount amount if no line item discounts exist
        if (!$this->hasLineItemDiscounts) {
            $totalDiscountAmount = $this->getTotalDiscountAmount();
            if (is_numeric($totalDiscountAmount) && $totalDiscountAmount > 0) {
                $amountDetails['discount_amount'] = $totalDiscountAmount;
            }
        }

        return $amountDetails;
    }

    public function getPaymentDetails(): array
    {
        $paymentDetails = [];

        // Always include order reference
        $orderReference = $this->getOrderReference();
        if (!empty($orderReference)) {
            $paymentDetails['order_reference'] = $orderReference;
        }

        // Include customer reference if available
        $customerReference = $this->getCustomerReference();
        if (!empty($customerReference)) {
            $paymentDetails['customer_reference'] = $customerReference;
        }

        return $paymentDetails;
    }

    /**
     * The calculated amount should be
     *
     * Sum(
     *   line_items[#].unit_cost * line_items[#].quantity +
     *   line_items[#].tax.total_tax_amount -
     *   line_items[#].discount_amount
     * ) + shipping.amount
     *
     * or
     *
     * Sum(line_items[#].unit_cost * line_items[#].quantity) +
     *   tax.total_tax_amount +
     *   shipping.amount -
     *   discount_amount
     */
    public function isOrderGrandTotalEqualToLineItemsTotal($data)
    {
        $this->loggerHelper->log($data);
        $order = $this->order;
        $this->loggerHelper->log($order->getGrandTotal());
        $calculatedAmount = 0;

        if (isset($data['amount_details']['line_items']) && is_array($data['amount_details']['line_items']))
        {
            foreach ($data['amount_details']['line_items'] as $item)
            {
                $lineItemTotal = $item['unit_cost'] * $item['quantity'];

                if (isset($item['tax']['total_tax_amount']) && is_numeric($item['tax']['total_tax_amount']))
                {
                    $lineItemTotal += $item['tax']['total_tax_amount'];
                }

                if (isset($item['discount_amount']) && is_numeric($item['discount_amount']))
                {
                    $lineItemTotal -= $item['discount_amount'];
                }

                $calculatedAmount += $lineItemTotal;
            }
        }

        if (isset($data['amount_details']['shipping']['amount']) && is_numeric($data['amount_details']['shipping']['amount']))
        {
            $calculatedAmount += $data['amount_details']['shipping']['amount'];
        }

        if (isset($data['amount_details']['tax']['total_tax_amount']) && is_numeric($data['amount_details']['tax']['total_tax_amount']))
        {
            $calculatedAmount += $data['amount_details']['tax']['total_tax_amount'];
        }

        if (isset($data['amount_details']['discount_amount']) && is_numeric($data['amount_details']['discount_amount']))
        {
            $calculatedAmount -= $data['amount_details']['discount_amount'];
        }

        $orderGrandTotal = $this->helper->convertMagentoAmountToStripeAmount(
            $order->getGrandTotal(),
            $order->getOrderCurrencyCode()
        );

        return ($calculatedAmount == $orderGrandTotal);
    }
}
