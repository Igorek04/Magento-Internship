define([
    'uiComponent',
    'Perspective_AsyncCatalog/js/model/catalog-data',
    'Magento_Catalog/js/price-utils',
    'ko',
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Catalog/js/catalog-add-to-cart'
], function (Component, catalogData, priceUtils, ko, $, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Perspective_AsyncCatalog/catalog/products/renderer',
            displayMode: 'grid'
        },

        products: catalogData.products,
        isLoading: catalogData.isLoading,

        initialize: function () {
            var self = this;
            this._super();

            this.displayMode = ko.observable(this.displayMode);

            catalogData.loadFilters();

            catalogData.loadProducts().done(function() {
                setTimeout(function() {
                    var forms = $('form[data-role="tocart-form"]');

                    if (forms.length > 0) {
                        forms.catalogAddToCart();
                    }

                    $('body').trigger('contentUpdated');
                }, 200);
            });

            return this;
        },

        formatPrice: function (priceData) {
            if (!priceData) return '';

            var format = {
                decimalSymbol: '.',
                groupSymbol: ',',
                precision: 2,
                integerRequired: false,
                pattern: priceData.currency === 'USD' ? '$%s' : '%s ' + priceData.currency
            };

            return priceUtils.formatPrice(priceData.value, format);
        },

        getFormKey: function() {
            return $.cookie('form_key');
        },

        getUenc: function() {
            return btoa(window.location.href)
                .replace(/\+/g, '-')
                .replace(/\//g, '_')
                .replace(/=+$/, '');
        },

        getAddToCartUrl: function(productId) {
            return window.BASE_URL + 'checkout/cart/add/uenc/' + this.getUenc() + '/product/' + productId + '/';
        },

        addToWishlist: function (productId) {
            var self = this;
            var url = window.BASE_URL + 'wishlist/index/add/product/' + productId + '/';

            $.ajax({
                url: url,
                data: {
                    'product': productId,
                    'uenc': self.getUenc(),
                    'form_key': self.getFormKey()
                },
                type: 'post',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                showLoader: true,
                success: function (res) {
                    // Сценарий 1: Сервер прислал JSON (все ок или управляемый редирект)
                    if (res && res.backUrl) {
                        window.location.assign(res.backUrl);
                        return;
                    }
                    this._updateSections();
                }.bind(this),

                error: function (res) {
                    console.log('Wishlist request status:', res.status);

                    if (res.status === 200 && res.responseText.includes('customer-account-login')) {
                        window.location.assign(window.BASE_URL + 'customer/account/login/');
                    } else {
                        this._updateSections();
                    }
                }.bind(this)
            });
        },

        _updateSections: function() {
            var sections = ['wishlist', 'compare-products', 'messages'];
            customerData.invalidate(sections);
            customerData.reload(sections, true);
        }
    });
});
