define([
    'async-catalog-core',
    'jquery',
    'jquery/jquery.cookie',
    'Magento_Catalog/js/catalog-add-to-cart'
], function (Core, $) {
    'use strict';

    Core.register('catalogAddToCart', (root, config) => ({

        init() {
            root.$watch('catalogData.products', () => {
                this.reinitCart();
            });
        },

        getFormKey() {
            return $.cookie('form_key');
        },

        getUenc() {
            return btoa(window.location.href)
                .replace(/\+/g, '-')
                .replace(/\//g, '_')
                .replace(/=+$/, '');
        },

        getAddToCartUrl(productId) {
            return window.BASE_URL + 'checkout/cart/add/uenc/' + this.getUenc() + '/product/' + productId + '/';
        },

        reinitCart() {
            root.$nextTick(() => {
                $('form[data-role="tocart-form"]').each(function () {
                    const $form = $(this);
                    if (!$form.data('mage-catalog-add-to-cart')) {
                        $form.catalogAddToCart();
                    }
                });
                $('body').trigger('contentUpdated');
            });
        },

    }));
});
