<?php

namespace StripeIntegration\Payments\Plugin\Sales\Model;

use StripeIntegration\Payments\Helper\Dispute;
use StripeIntegration\Payments\Helper\Radar;

class Order
{
    private $orders = [];
    private $config;
    private $checkoutFlow;
    private $paymentHelper;
    private $areaCodeHelper;
    private $orderHelper;
    private $stripePaymentIntentModelFactory;
    private $radarHelper;

    public function __construct(
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Model\Checkout\Flow $checkoutFlow,
        \StripeIntegration\Payments\Model\Stripe\PaymentIntentFactory $stripePaymentIntentModelFactory,
        \StripeIntegration\Payments\Helper\Payment $paymentHelper,
        \StripeIntegration\Payments\Helper\AreaCode $areaCodeHelper,
        \StripeIntegration\Payments\Helper\Order $orderHelper,
        Radar $radarHelper
    ) {
        $this->config = $config;
        $this->checkoutFlow = $checkoutFlow;
        $this->stripePaymentIntentModelFactory = $stripePaymentIntentModelFactory;
        $this->paymentHelper = $paymentHelper;
        $this->areaCodeHelper = $areaCodeHelper;
        $this->orderHelper = $orderHelper;
        $this->radarHelper = $radarHelper;
    }

    public function afterCanCancel($order, $result)
    {
        if (isset($this->orders[$order->getIncrementId()]) && !$this->areaCodeHelper->isTesting())
            return $this->orders[$order->getIncrementId()];

        if ($this->checkoutFlow->isCleaningExpiredOrders)
        {
            $this->config->reInitStripe($order->getStoreId(), $order->getOrderCurrencyCode(), null);
        }

        if ($order->getState() === Radar::MANUAL_REVIEW_STATE_CODE) {
            return $this->orders[$order->getIncrementId()] = false;
        }

        $method = $order->getPayment()->getMethod();

        if ($method == "stripe_payments_checkout")
        {
            return $this->orders[$order->getIncrementId()] = $this->canCancelStripeCheckout($order, $result);
        }
        else if ($method == "stripe_payments_bank_transfers")
        {
            return $this->orders[$order->getIncrementId()] = $this->canCancelFromAdminOnly($result);
        }
        else if ($method == "stripe_payments_invoice")
        {
            return $this->orders[$order->getIncrementId()] = $this->canCancelFromAdminOnly($result);
        }
        else if ($method == "stripe_payments")
        {
            return $this->orders[$order->getIncrementId()] = $this->canCancelStripePayments($order, $result);
        }
        else
        {
            return $this->orders[$order->getIncrementId()] = $result;
        }
    }

    public function beforeCancel($order)
    {
        if ($this->checkoutFlow->isCleaningExpiredOrders)
        {
            $paymentMethodCode = $order->getPayment()->getMethodInstance()->getCode();
            if (strpos($paymentMethodCode, "stripe_") !== false)
            {
                $this->orderHelper->addOrderComment(__("The order was canceled via cron because it expired as per the Pending Payment Order Lifetime setting."), $order);

                $invoices = $order->getInvoiceCollection();
                foreach ($invoices as $invoice)
                {
                    if ($invoice->canCancel())
                    {
                        $invoice->cancel();
                        $invoice->save();
                    }
                }
            }
        }
    }

    public function afterCanInvoice($subject, $result)
    {
        if ($subject->getState() === Dispute::STRIPE_DISPUTE_STATE_CODE ||
            $subject->getState() === Radar::MANUAL_REVIEW_STATE_CODE
        ) {
            return false;
        }

        return $result;
    }

    public function afterCanShip($subject, $result)
    {
        if ($subject->getState() === Dispute::STRIPE_DISPUTE_STATE_CODE ||
            $subject->getState() === Radar::MANUAL_REVIEW_STATE_CODE
        ) {
            return false;
        }

        return $result;
    }

    public function afterCanEdit($subject, $result)
    {
        return $this->radarHelper->resolveManualReviewActionPermission($subject, $result);
    }
    public function afterCanCreditmemo($subject, $result)
    {
        if ($subject->getState() === Dispute::STRIPE_DISPUTE_STATE_CODE) {
            // The only time when you will not be able to create a manual credit memo is when all the order amount is
            // paid and there is only one invoice.
            if ($subject->getTotalDue() == 0 && count($subject->getInvoiceCollection()) == 1) {
                return false;
            }
        }

        return $this->radarHelper->resolveManualReviewActionPermission($subject, $result) ;
    }

    public function afterCanVoidPayment($subject, $result)
    {
        return $this->radarHelper->resolveManualReviewActionPermission($subject, $result);
    }

    public function afterCanHold($subject, $result)
    {
        return $this->radarHelper->resolveManualReviewActionPermission($subject, $result);
    }

    public function afterCanUnHold($subject, $result)
    {
        return $this->radarHelper->resolveManualReviewActionPermission($subject, $result);
    }

    public function afterCanReviewPayment($subject, $result)
    {
        return $this->radarHelper->resolveManualReviewActionPermission($subject, $result);
    }

    public function afterCanFetchPaymentReviewUpdate($subject, $result)
    {
        return $this->radarHelper->resolveManualReviewActionPermission($subject, $result);
    }
    public function afterCanReorderIgnoreSalable($subject, $result)
    {
        return $this->radarHelper->resolveManualReviewActionPermission($subject, $result);
    }
    public function afterCanReorder($subject, $result)
    {
        return $this->radarHelper->resolveManualReviewActionPermission($subject, $result);
    }

    private function canCancelStripeCheckout($order, $result)
    {
        if (!$this->areaCodeHelper->isAdmin())
            return $result;

        $checkoutSessionId = $order->getPayment()->getAdditionalInformation("checkout_session_id");
        if (empty($checkoutSessionId))
            return $result;

        $stripe = $this->config->getStripeClient();

        if (empty($stripe))
            return $result;

        try
        {
            $checkoutSession = $stripe->checkout->sessions->retrieve($checkoutSessionId, []);

            if ($checkoutSession->status == "open")
            {
                // Stripe Checkout sessions expire after 24 hours, or when the customer session expires, whichever comes first.
                // The order should not be cancelable during this timeframe.
                return false;
            }
            else if (!empty($checkoutSession->payment_intent))
            {
                $stripePaymentIntentModel = $this->stripePaymentIntentModelFactory->create()->fromPaymentIntentId($checkoutSession->payment_intent);
                if ($stripePaymentIntentModel->wasSuccessfullyAuthorized())
                {
                    return false;
                }
                else if ($this->areOrderInvoicesOpen($order))
                {
                    // An invoice was created during order placement, which makes the order non-cancelable,
                    // however the payment was never authorized, in which case we want to be able to cancel the order.
                    return true;
                }
                else
                {
                    return $result;
                }
            }
            else
            {
                return $result;
            }
        }
        catch (\Exception $e)
        {
            return $result;
        }
    }

    private function canCancelFromAdminOnly($result)
    {
        if (!$this->areaCodeHelper->isAdmin())
            return false;

        return $result;
    }

    private function canCancelStripePayments($order, $result)
    {
        if ($result && $this->checkoutFlow->isCleaningExpiredOrders)
        {
            $stripePaymentIntentModel = $this->paymentHelper->getStripePaymentIntentModel($order);

            if ($stripePaymentIntentModel->wasSuccessfullyAuthorized())
            {
                return false;
            }
        }
        else if (!$result && $this->checkoutFlow->isCleaningExpiredOrders)
        {
            $stripePaymentIntentModel = $this->paymentHelper->getStripePaymentIntentModel($order);

            if ($stripePaymentIntentModel->wasSuccessfullyAuthorized())
            {
                return false;
            }
            else if ($this->areOrderInvoicesOpen($order))
            {
                // An invoice was created during order placement, which makes the order non-cancelable,
                // however the payment was never authorized, in which case we want to be able to cancel the order.
                return true;
            }
        }

        return $result;
    }

    private function areOrderInvoicesOpen($order)
    {
        $invoices = $order->getInvoiceCollection();
        if ($invoices->count() == 0)
            return false;

        foreach ($invoices as $invoice)
        {
            if ($invoice->getState() != \Magento\Sales\Model\Order\Invoice::STATE_OPEN)
                return false;
        }

        return true;
    }
}
