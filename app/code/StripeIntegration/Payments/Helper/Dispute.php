<?php

namespace StripeIntegration\Payments\Helper;

class Dispute
{
    public const STRIPE_DISPUTE_STATUS_CODE = 'stripe_disputed';
    public const STRIPE_DISPUTE_STATE_CODE = 'stripe_disputed';

    public function isDisputeInquiry($disputeObject)
    {
        return $disputeObject['payment_method_details']['type'] === 'card' && $disputeObject['payment_method_details']['card']['case_type'] === 'inquiry';
    }

    public function isDisputeChargeback($disputeObject)
    {
        return $disputeObject['payment_method_details']['type'] === 'card' && $disputeObject['payment_method_details']['card']['case_type'] === 'chargeback';
    }

    public function isDisputeCompliance($disputeObject)
    {
        return $disputeObject['payment_method_details']['type'] === 'card' && $disputeObject['payment_method_details']['card']['case_type'] === 'compliance';
    }

    public function isChargeRefunded($charge, $disputeObject)
    {
        return $disputeObject['is_charge_refundable'] && $charge->refunded;
    }

    public function isDisputeLost($disputeObject)
    {
        return $disputeObject['status'] === 'lost';
    }

    public function isDisputeWon($disputeObject)
    {
        return $disputeObject['status'] === 'won';
    }

    public function isInquiryClosed($disputeObject)
    {
        return $disputeObject['status'] === 'warning_closed';
    }

    public function hasWithdrawnAmount($disputeObject)
    {
        foreach ($disputeObject['balance_transactions'] as $transaction) {
            if ($transaction['reporting_category'] === 'dispute') {
                return true;
            }
        }

        return false;
    }

    public function getFullWithdrawnAmount($disputeObject)
    {
        $fullWithdrawnAmount = 0;
        foreach ($disputeObject['balance_transactions'] as $transaction) {
            if ($transaction['reporting_category'] === 'dispute') {
                $fullWithdrawnAmount += $transaction['net'];
            }
        }

        return abs($fullWithdrawnAmount);
    }

    public function hasReinstatedAmount($disputeObject)
    {
        foreach ($disputeObject['balance_transactions'] as $transaction) {
            if ($transaction['reporting_category'] === 'dispute_reversal') {
                return true;
            }
        }

        return false;
    }

    public function getFullReinstatedAmount($disputeObject)
    {
        $fullWithdrawnAmount = 0;
        foreach ($disputeObject['balance_transactions'] as $transaction) {
            if ($transaction['reporting_category'] === 'dispute_reversal') {
                $fullWithdrawnAmount += $transaction['net'];
            }
        }

        return abs($fullWithdrawnAmount);
    }

    /**
     * When using this method, use === to check the result
     *
     * @param $order
     * @return bool|null
     */
    public function canManualCreditMemoWhileDisputed($order)
    {
        if ($order->getState() === Dispute::STRIPE_DISPUTE_STATE_CODE) {
            // The only time when you will not be able to create a manual credit memo is when all the order amount is
            // paid and there is only one invoice.
            if ($order->getTotalDue() == 0 && count($order->getInvoiceCollection()) == 1) {
                return false;
            } else {
                return true;
            }
        }

        return null;
    }
}