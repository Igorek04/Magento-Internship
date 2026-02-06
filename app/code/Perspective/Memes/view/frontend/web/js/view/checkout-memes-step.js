define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Customer/js/model/customer',
        'jquery'
    ],
    function (
        ko,
        Component,
        _,
        stepNavigator,
        customer,
        $
    ) {
        'use strict';
        /**
         * check-login - is the name of the component's .html template
         */
        return Component.extend({
            defaults: {
                template: 'Perspective_Memes/memes'
            },

            isVisible: ko.observable(false),
            stepCode: 'memesStepCode',
            stepTitle: 'Memes',

            memesList: ko.observableArray([]),
            selectedMeme: ko.observable(null),

            initialize: function () {
                this._super();
                let self = this;
                // get quote memes from config provider
                let config = window.checkoutConfig.memesData;

                // if config provider have memes
                if (config.items && config.items.length) {
                    self.memesList(config.items);
                    self.selectedMeme(config.selected || null);

                    //register memes step
                    self.isVisible(true);
                    stepNavigator.registerStep(
                        this.stepCode,
                        null,
                        this.stepTitle,
                        this.isVisible,
                        _.bind(this.navigate, this),
                        15
                    );
                }

                this.selectedMeme.subscribe(function(selected) {
                    self.updateSelectedMeme(selected);
                });

                return this;
            },

            navigate: function () {},
            navigateToNextStep: function () { stepNavigator.next(); },

            updateSelectedMeme: function (memeUrl) {
                let self = this;
                self.selectedMeme(memeUrl);

                $.ajax({
                    url: '/memes/ajax/updateselectedmeme',
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true,
                    data: {
                        maskedQuoteId: window.checkoutConfig.quoteData.entity_id,
                        selected: memeUrl,
                        entityType: 'quote'
                    },
                    success: function (res) {
                        if (res.success) {
                            window.checkoutConfig.memesData.selected = res.selected;
                            console.log('Selected meme saved');
                        }
                    },
                    error: function (err) {
                        console.error('Selected meme error', err);
                    }
                });
            }
        });
    });
