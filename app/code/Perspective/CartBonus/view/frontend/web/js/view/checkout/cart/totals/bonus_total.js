define(
    [
        'Perspective_CartBonus/js/view/checkout/summary/bonus_total'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            /**
             * @override
             */
            isDisplayed: function () {
                return this.getPureValue() !== 0;
            },

            /*getMessages: function () {
                ret
            }*/
        });
    }
);
