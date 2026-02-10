define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Customer/js/model/customer',
        'jquery',
        'Perspective_Memes/js/view/meme-picker'
    ],
    function (
        ko,
        Component,
        _,
        stepNavigator,
        customer,
        $,
        memePickerInit
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Perspective_Memes/checkout-memes-select'
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

                    // react component meme picker
                    self.isVisible.subscribe(function(isVisible) {
                        if (isVisible) {
                            const element = document.getElementById('react-meme-picker');
                            if (element) {
                                memePickerInit({
                                    memes: config,
                                    quoteId: window.checkoutConfig.quoteData.entity_id
                                }, element);
                            }
                        }
                    });
                }
                return this;
            },
            navigate: function () {},
            navigateToNextStep: function () { stepNavigator.next(); },
        });
    });
