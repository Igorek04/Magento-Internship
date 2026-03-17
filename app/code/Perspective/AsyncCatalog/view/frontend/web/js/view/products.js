define([
    'uiComponent',
    'Perspective_AsyncCatalog/js/model/catalog-data',
    'Magento_Catalog/js/price-utils'
], function (Component, catalogData, priceUtils) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Perspective_AsyncCatalog/catalog/products'
        },

        products: catalogData.products,
        isLoading: catalogData.isLoading,

        initialize: function () {
            this._super();
            catalogData.loadFilters();
            catalogData.loadProducts();
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
        }
    });
});
