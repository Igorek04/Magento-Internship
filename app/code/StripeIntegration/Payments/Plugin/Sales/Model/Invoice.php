<?php

namespace StripeIntegration\Payments\Plugin\Sales\Model;

use StripeIntegration\Payments\Helper\Radar;

class Invoice
{
    private $transactions = [];
    private $transactionSearchResultFactory;
    private $subscriptionProductFactory;
    private $radarHelper;

    public function __construct(
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactionSearchResultFactory,
        \StripeIntegration\Payments\Model\SubscriptionProductFactory $subscriptionProductFactory,
        Radar $radarHelper
    )
    {
        $this->transactionSearchResultFactory = $transactionSearchResultFactory;
        $this->subscriptionProductFactory = $subscriptionProductFactory;
        $this->radarHelper = $radarHelper;
    }

    public function getTransactions($order)
    {
        if (isset($this->transactions[$order->getId()]))
            return $this->transactions[$order->getId()];

        $transactions = $this->transactionSearchResultFactory->create()->addOrderIdFilter($order->getId());
        return $this->transactions[$order->getId()] = $transactions;
    }

    public function hasSubscriptions($subject)
    {
        $items = $subject->getAllItems();

        foreach ($items as $item)
        {
            if (!$item->getProductId())
                continue;

            if ($this->subscriptionProductFactory->create()->fromProductId($item->getProductId())->isSubscriptionProduct())
                return true;
        }

        return false;
    }

    public function afterCanCancel($subject, $result)
    {
        return $this->radarHelper->resolveManualReviewActionPermission($subject->getOrder(), $result);
    }

    public function afterCanCapture($subject, $result)
    {
        return $this->radarHelper->resolveManualReviewActionPermission($subject->getOrder(), $result);
    }
    public function afterCanVoid($subject, $result)
    {
        return $this->radarHelper->resolveManualReviewActionPermission($subject->getOrder(), $result);
    }
}
