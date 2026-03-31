define(['async-catalog-core', 'Magento_Catalog/js/price-utils'], function (Core, priceUtils) {
    'use strict';

    Core.register('productPrice', (root, config) => ({


        init() {
        },

        formatPrice(priceData) {
            if (!priceData || priceData.value === undefined) {
                return '';
            }

            const format = {
                decimalSymbol: '.',
                groupSymbol: ',',
                precision: 2,
                integerRequired: false,
                pattern: priceData.currency === 'USD' ? '$%s' : '%s ' + priceData.currency
            };

            return priceUtils.formatPrice(priceData.value, format);
        }
    }));
});
