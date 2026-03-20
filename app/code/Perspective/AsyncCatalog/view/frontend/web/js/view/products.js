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
            pageConfig: {}
        },

        products: catalogData.products,
        isLoading: catalogData.isLoading,
        currentMode: catalogData.currentMode,

        initialize: function () {
            var self = this;
            this._super();

            var config = this.pageConfig;
            if (config) {
                catalogData.availableModes(config.availableModes);
                catalogData.availableSortList(config.availableSortList);
                catalogData.currentSortField(config.defaultSortBy);

                var initialMode = config.currentMode || 'grid';
                var isGrid = (initialMode === 'grid');

                catalogData.pageSize(isGrid ? config.gridPerPageDefault : config.listPerPageDefault);
                catalogData.availablePages(isGrid ? config.gridPerPageValues : config.listPerPageValues);

                catalogData.currentMode.subscribe(function (newMode) {
                    var isGrid = (newMode === 'grid');
                    catalogData.pageSize(isGrid ? config.gridPerPageDefault : config.listPerPageDefault);
                    catalogData.availablePages(isGrid ? config.gridPerPageValues : config.listPerPageValues);
                    catalogData.currentPage(1);
                    catalogData.loadProducts().done(function() {
                        self.reinitCart();
                    });
                });

                catalogData.currentMode(initialMode);

            }
            console.log(config);

            this.products.subscribe(function () {
                self.reinitCart();
            });

            catalogData.loadFilters();
            //catalogData.loadProducts().done(function() {
            //    self.reinitCart();
            //});

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

        reinitCart: function () {
            setTimeout(function () {
                var forms = $('form[data-role="tocart-form"]');
                forms.each(function () {
                    var $form = $(this);
                    if (!$form.data('mage-catalog-add-to-cart')) {
                        $form.catalogAddToCart();
                    }
                });
                $('body').trigger('contentUpdated');
            }, 500);
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
                showLoader: true,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },

                complete: function (res) {
                    if (res.responseText && res.responseText.includes('customer-account-login')) {
                        window.location.assign(window.BASE_URL + 'customer/account/login/');
                        return;
                    }

                    var sections = ['wishlist', 'messages'];
                    customerData.invalidate(sections);
                    customerData.reload(sections, true).done(function() {
                        var messages = $.cookieStorage.get('mage-messages');
                        if (messages && messages.length > 0) {
                            customerData.set('messages', { messages: messages });
                            $.cookieStorage.set('mage-messages', '');
                        }
                    });
                }
            });
        },

        addToCompare: function (productId) {
            var self = this;
            var url = window.BASE_URL + 'catalog/product_compare/add/product/' + productId + '/';

            $.ajax({
                url: url,
                data: {
                    'product': productId,
                    'uenc': self.getUenc(),
                    'form_key': self.getFormKey()
                },
                type: 'post',
                showLoader: true,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },

                complete: function (res) {
                    var sections = ['compare-products', 'messages'];

                    customerData.invalidate(sections);
                    customerData.reload(sections, true).done(function() {
                        var messages = $.cookieStorage.get('mage-messages');
                        if (messages && messages.length > 0) {
                            customerData.set('messages', { messages: messages });
                            $.cookieStorage.set('mage-messages', '');
                        }
                    });
                }
            });
        },

        _updateSections: function() {
            var sections = ['wishlist', 'compare-products', 'messages'];
            customerData.invalidate(sections);
            customerData.reload(sections, true);
        }
    });
});
