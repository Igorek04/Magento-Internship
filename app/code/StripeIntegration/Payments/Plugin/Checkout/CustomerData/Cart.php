<?php

namespace StripeIntegration\Payments\Plugin\Checkout\CustomerData;

use Magento\Store\Model\StoreManagerInterface;
use StripeIntegration\Payments\Helper\Stripe\PaymentMethodMessagingElement;
use StripeIntegration\Payments\Helper\Config;

class Cart
{
    public const DISPLAY_IN_MINICART_PATH = 'payment/stripe_payments_payment_method_messaging/show_in_minicart';
    public const DISPLAY_IN_CART_PATH = 'payment/stripe_payments_payment_method_messaging/show_in_shopping_cart';
    private $messagingElementHelper;
    private $configHelper;
    private $storeManager;

    public function __construct(
        PaymentMethodMessagingElement $messagingElementHelper,
        Config $configHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->messagingElementHelper = $messagingElementHelper;
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
    }

    public function afterGetSectionData(
        \Magento\Checkout\CustomerData\Cart $subject,
        array $result
    ) {
        $result['messagingElement'] = $this->messagingElementHelper->getPaymentMethodMessagingOptions();
        $result['showMessagingElement'] = [
            'displayInMinicart' => (bool)$this->configHelper->getConfigData(self::DISPLAY_IN_MINICART_PATH, $this->storeManager->getStore()->getCode()),
            'displayInCart' => (bool)$this->configHelper->getConfigData(self::DISPLAY_IN_CART_PATH, $this->storeManager->getStore()->getCode())
        ];

        return $result;
    }
}