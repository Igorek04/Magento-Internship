<?php

namespace StripeIntegration\Payments\Helper;

use StripeIntegration\Payments\Exception\GenericException;
use Magento\Sales\Model\Order\CreditmemoFactory;

class Creditmemo
{
    private $creditmemoRepository;
    private $creditmemoManagement;
    private $creditmemoFactory;

    public function __construct(
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement,
        CreditmemoFactory $creditmemoFactory
    ) {
        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoManagement = $creditmemoManagement;
        $this->creditmemoFactory = $creditmemoFactory;
    }

    public function saveCreditmemo($creditmemo)
    {
        return $this->creditmemoRepository->save($creditmemo);
    }

    public function refundCreditmemo($creditmemo, $offline = false)
    {
        $this->creditmemoManagement->refund($creditmemo, $offline);
    }

    public function sendEmail($creditmemoId)
    {
        $this->creditmemoManagement->notify($creditmemoId);
    }

    public function validateBaseRefundAmount($order, $baseAmount)
    {
        if (!$order->canCreditmemo())
        {
            throw new GenericException("The order cannot be refunded");
        }

        if ($baseAmount <= 0)
        {
            throw new GenericException("Cannot refund an amount of $baseAmount.");
        }
    }

    public function createOfflineCreditmemoForInvoice($invoice, $order)
    {
        // Prepare credit memo data
        $creditmemo = $this->creditmemoFactory->createByOrder($order);
        $creditmemo->setInvoice($invoice);

        // Refund to the customer and save credit memo
        $this->refundCreditmemo($creditmemo, true);

        return $creditmemo;
    }

    public function createOnlineCreditmemoForInvoice($invoice, $order)
    {
        // Prepare credit memo data
        $creditmemo = $this->creditmemoFactory->createByOrder($order);
        $creditmemo->setInvoice($invoice);

        // Refund to the customer and save credit memo
        $this->refundCreditmemo($creditmemo);

        return $creditmemo;
    }
}
