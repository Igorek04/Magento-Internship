define(['jquery'], function ($) {
    'use strict';

    $(document).on('ajax:addToCart', function (event, data) {
        if (typeof _paq === 'undefined') {
            console.warn('Matomo tracker is not loaded');
            return;
        }

        var sku = data.sku || 'unknown',
            form = data.form,
            parentElement = form.closest('.product-item-info, .product-info-main'),
            parentId = parentElement.attr('id') || 'no-id',
            parentClass = parentElement.attr('class') || 'no-class',
            timestamp = new Date().toISOString();

        _paq.push(['trackEvent',
            'Ecommerce',
            'AddToCart',
            sku,
            1,
            {
                dimension1: timestamp,
                dimension2: parentId,
                dimension3: parentClass
            }
        ]);

        console.log('Matomo: AddToCart tracked', {
            sku: sku,
            timestamp: timestamp,
            parent_id: parentId,
            parent_class: parentClass
        });
    });
});
