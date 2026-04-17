define([
    'Magento_Ui/js/form/components/insert-form'
], function (Insert) {
    'use strict';

    return Insert.extend({
        defaults: {
            listens: {
                responseData: 'onResponse'
            },
            modules: {
                answerListing: '${ $.answerListingProvider }',
                answerModal: '${ $.answerModalProvider }'
            }
        },

        onResponse: function (responseData) {
            if (!responseData.error) {
                this.answerModal().closeModal();
                this.answerListing().reload({
                    refresh: true
                });
            }
        }
    });
});
