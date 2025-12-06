<?php

declare(strict_types=1);

namespace StripeIntegration\Payments\Model\Stripe\Event;

use StripeIntegration\Payments\Helper\Dispute;
use StripeIntegration\Payments\Helper\Webhooks;
use StripeIntegration\Payments\Helper\Order;
use StripeIntegration\Payments\Helper\Convert;
use StripeIntegration\Payments\Helper\Currency;
use StripeIntegration\Payments\Model\Stripe\Service\StripeObjectServicePool;
use StripeIntegration\Payments\Model\Stripe\StripeObjectTrait;

class ChargeDisputeCreated
{
    use StripeObjectTrait;

    private $webhooksHelper;
    private $orderHelper;
    private $disputeHelper;
    private $convertHelper;
    private $currencyHelper;

    public function __construct(
        StripeObjectServicePool $stripeObjectServicePool,
        Webhooks $webhooksHelper,
        Order $orderHelper,
        Dispute $disputeHelper,
        Convert $convertHelper,
        Currency $currencyHelper
    ) {
        $stripeObjectService = $stripeObjectServicePool->getStripeObjectService('events');
        $this->setData($stripeObjectService);

        $this->webhooksHelper = $webhooksHelper;
        $this->orderHelper = $orderHelper;
        $this->disputeHelper = $disputeHelper;
        $this->convertHelper = $convertHelper;
        $this->currencyHelper = $currencyHelper;
    }

    /**
     * Process the dispute creation event:
     *      - a comment is added to the order, and it is set in the stripe_disputed state
     *      - if the dispute includes a withdrawal of funds, a comment is added to reflect this fact
     *
     * @param $arrEvent
     * @param $object
     * @return void
     * @throws \StripeIntegration\Payments\Exception\MissingOrderException
     * @throws \StripeIntegration\Payments\Exception\WebhookException
     */
    public function process($arrEvent, $object)
    {
        $order = $this->webhooksHelper->loadOrderFromEvent($arrEvent);
        $this->webhooksHelper->detectRaceCondition($order->getIncrementId(), ['charge.refunded', 'charge.succeeded']);
        $currency = $object['currency'];
        $disputeAmount = $this->convertHelper->stripeAmountToMagentoAmount($object['amount'], $currency);

        $order->setHoldBeforeState($order->getState());
        $order->setHoldBeforeStatus($order->getStatus());

        $comment = __(
            'A dispute %1 has been opened for %2 with the reason %3.',
            $object['id'],
            $this->currencyHelper->addCurrencySymbol($disputeAmount, $currency),
            ucfirst(str_replace("_", " ", $object['reason']))
        );
        $order->setState(Dispute::STRIPE_DISPUTE_STATE_CODE)
            ->setStatus(Dispute::STRIPE_DISPUTE_STATUS_CODE)
            ->addStatusToHistory(Dispute::STRIPE_DISPUTE_STATUS_CODE, $comment, false);

        if ($this->disputeHelper->isDisputeChargeback($object) || $this->disputeHelper->isDisputeCompliance($object)) {
            if ($this->disputeHelper->hasWithdrawnAmount($object)) {
                $fullWithdrawnStripeAmount = $this->disputeHelper->getFullWithdrawnAmount($object);
                $fullWithdrawnMagentoAmount = $this->convertHelper->stripeAmountToMagentoAmount($fullWithdrawnStripeAmount, $currency);

                $comment = __('The amount of %1 (%2 including fees) was withdrawn from the Stripe account.',
                    $this->currencyHelper->addCurrencySymbol($disputeAmount, $currency),
                    $this->currencyHelper->addCurrencySymbol($fullWithdrawnMagentoAmount, $currency)
                );
                $this->orderHelper->addOrderComment($comment, $order);
            }
        }

        $this->orderHelper->saveOrder($order);
    }

}