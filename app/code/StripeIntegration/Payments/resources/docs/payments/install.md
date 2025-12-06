---
title: Use the Stripe app for Adobe Commerce (Magento 2)
subtitle: Learn how to install, upgrade, and uninstall the Stripe app for Adobe Commerce (Magento 2).
route: /use-stripe-apps/adobe-commerce/payments/install
redirects:
  - /magento/install
  - /plugins/magento/install
  - /plugins/magento-2/install
  - /plugins/adobe-commerce/install
  - /use-stripe-apps/adobe-commerce/install
  - /connectors/adobe-commerce/payments/install
  - /use-stripe-apps/adobe-commerce/payments/versions
  - /use-stripe-apps/adobe-commerce/versions
  - /connectors/adobe-commerce/payments/versions
stripe_products: []
target_locales: ['es-ES', 'fr-CA', 'it-IT']
---

{% callout type="caution" %}
We recommend that you test the module before installing it in your production environment. If you have an installation issue, see [Troubleshooting](/use-stripe-apps/adobe-commerce/payments/troubleshooting).
{% /callout %}

## Install the module {% #install %}

{% tabs pref="integration" defaultValue="marketplace" %}

{% tab title="From the Marketplace (recommended)" id="marketplace" %}

1. Place an order for the module through the [Adobe Marketplace](https://marketplace.magento.com/stripe-stripe-payments.html).

1. Open a terminal and run the following command in your Adobe Commerce directory:

    ```bash
    composer require stripe/stripe-payments
    ```

At this stage, you might have to submit your username and password. Provide your [Adobe Commerce authentication keys](https://devdocs.magento.com/guides/v2.3/install-gde/prereq/connect-auth.html). You can accept to save your credentials when prompted by Composer. If you've saved your keys and see the error `Invalid Credentials`, update your keys in `~/.composer/auth.json` or delete this file and run the command again.

1. Set up the module by running the following commands:

    ```bash
    php bin/magento setup:upgrade
    php bin/magento cache:flush
    php bin/magento cache:clean
    ```

1. If you run Adobe Commerce in production mode, you must also compile and deploy the module's static files.

```bash
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```
{% /tab %}

{% tab title="From the raw package" id="package" %}

1. Download the [latest version](https://github.com/stripe/stripe-magento2-releases/raw/master/stripe-magento2-latest.tgz) of the module from Stripe.

1. Extract the module in your Adobe Commerce directory.

    ```bash
    tar -xvf stripe-magento2-latest.tgz
    ```

1. Install the Stripe PHP library.

    ```bash
    composer require stripe/stripe-php:~17.6
    ```

1. Set up the module.

    ```bash
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento cache:flush
    ```

1. If you run Adobe Commerce in production mode, you must also compile and deploy the module's static files.

    ```bash
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy
    ```
{% /tab %}

{% /tabs %}

## Upgrade the module {% #upgrade %}

Before you upgrade:

- Back up your files and database.
- Start with your sandbox environment.
- Keep a copy of any customization you made to the module's original code.
- Check out the [CHANGELOG](https://github.com/stripe/stripe-magento2-releases/blob/master/CHANGELOG.md).

Patch releases (x.x.Y) are backward compatible and require no extra development on your side after you upgrade. Minor and major releases might add new features or change code in a backwards incompatible way. If you customized the module's code, you'll need to port these customizations after upgrading and resolve any potential conflict.

{% tabs pref="integration" %}

{% tab title="From the Marketplace" id="marketplace" %}

Run the following commands:

```bash
composer remove stripe/stripe-payments
composer require stripe/stripe-payments
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:clean
```
{% /tab %}

{% tab title="From the raw package" id="package" %}

Run the following commands:

```bash
php bin/magento module:disable --clear-static-content StripeIntegration_Payments
rm -rf app/code/StripeIntegration/Payments
tar -xvf stripe-magento2-latest.tgz
php bin/magento module:enable StripeIntegration_Payments
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
php bin/magento cache:cleanStripeIntegration_Payments
```
{% /tab %}

{% /tabs %}

## Uninstall the module {% #uninstall %}

Before you uninstall:

- Backup your files and database.
- Keep a copy of any customization you made to the module's original code in case you need to reinstall it later.

{% tabs pref="integration" %}

{% tab title="From the Marketplace" id="marketplace" %}

Run the following commands:

```bash
composer remove stripe/stripe-payments
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:clean
```
{% /tab %}

{% tab title="From the raw package" id="package" %}

Run the following commands:

```bash
php bin/magento module:disable --clear-static-content StripeIntegration_Payments
composer remove stripe/stripe-php
rm -rf app/code/StripeIntegration/Payments
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
php bin/magento cache:clean
```
{% /tab %}

{% /tabs %}

## Lifecycle policy

The latest version of the module supports the following versions of Adobe Commerce:

{% table %}
- Release
- Support
---
- Adobe Commerce 2.0 - 2.3.6
- No longer supported, the last compatible version is `stripe/stripe-payments:2.9.5`.
---
- Adobe Commerce 2.3.7 - 2.4.x
- Currently supported, see below for our own lifecycle policy.
{% /table %}

For `stripe/stripe-payments:4.5.*` and later, we provide bug fixes and security patches. Stripe recommends that you upgrade to the latest published version of the module. All releases are available in the Adobe Marketplace and in the [stripe-magento2-releases](https://github.com/stripe/stripe-magento2-releases) GitHub repository.

## Version history

Learn about backward compatibility changes in Adobe Commerce app versions (Magento 2).

### Version 4.1

Version 4.1.0 introduces changes to handle scenarios where orders could be placed and invoiced without collecting payment. These scenarios include:

- Purchases of trial subscriptions.
- Subscriptions with future start dates.
- Upgrades or downgrades of existing subscription plans.

Previously, the module automatically refunded and closed these orders with an offline credit memo. Now, to improve tax reporting:

- Automatic refunds are deprecated.
- Order items in these scenarios have a custom price of 0.
- These items are excluded from shipping amount calculations.

This results in orders with zero subtotal, shipping, and tax amounts. When regular products are purchased with subscriptions, the subtotal, shipping, and tax amounts only reflect the regular order items. These changes ensure the order grand total matches the collected payment amount on the order date.

Additionally, version 4.1 changes how partial captures and refunds are handled:

- Previously, partial captures or refunds from the Stripe dashboard automatically created invoices or credit memos in Magento.
- These documents didn't include order items, using manual totals or adjustment fees or refunds instead.
- This approach caused issues with tax reporting.

In version 4.1:

- Only full captures and full refunds result in automatic invoices or credit memos.
- For partial captures or refunds, merchants should use the Magento admin panel.
- This ensures correct tax breakdown on documents and accurate reporting to the Stripe API.

{% see-also %}
* [Configuring the Stripe app for Adobe Commerce](/use-stripe-apps/adobe-commerce/payments/configuration)
* [Using the Adobe Commerce admin panel](/use-stripe-apps/adobe-commerce/payments/admin)
* [Troubleshooting](/use-stripe-apps/adobe-commerce/payments/troubleshooting)
{% /see-also %}
