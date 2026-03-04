define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Perspective_Voting/checkout/totals/winners-discount-total',
                config: {}
            },
            totals: quote.getTotals(),

            isDisplayed: function() {
                let price = 0;
                if (this.totals()) {
                    price = totals.getSegment('perspective_voting_winners_discount_total').value;
                }
                return price !== 0;
            },

            getValue: function() {
                let price = 0;
                if (this.totals()) {
                    price = totals.getSegment('perspective_voting_winners_discount_total').value;
                }
                return this.getFormattedPrice(price);
            }
        });
    }
);
