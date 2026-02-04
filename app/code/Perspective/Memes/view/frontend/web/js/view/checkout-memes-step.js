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

            //add here your logic to display step,
            isVisible: ko.observable(true), //<
            stepCode: 'memesStepCode',
            stepTitle: 'Memes',

            memesList: ko.observableArray([]),
            selectedMeme: ko.observable(null),

            initialize: function () {
                this._super();
                let self = this;
                stepNavigator.registerStep(
                    this.stepCode,
                    null,
                    this.stepTitle,
                    this.isVisible,
                    _.bind(this.navigate, this),
                    15
                );
                this.isVisible.subscribe(function(visible) {
                    if (visible) {
                        self.loadMemes();
                    }
                });

                this.selectedMeme.subscribe(function(selected) {
                    self.saveSelectedMeme(selected);
                });

                return this;
            },

            navigate: function () {},
            navigateToNextStep: function () { stepNavigator.next(); },

            loadMemes: function () {
                let self = this;
                let config = window.checkoutConfig.memesData;

                // if provider have memes data items(memes url)
                if (config && config.items && config.items.length) {
                    self.memesList(config.items);
                    self.selectedMeme(config.selected || null);
                } else {
                    // else - ajax request
                    self.loadMemesFromController();
                }
                console.log(self.memesList);
                console.log(self.memesList());
            },

            //request to get list of memes url from api поменять
            loadMemesFromController: function () {
                let self = this;
                $.ajax({
                    url: '/memes/ajax/getmemedata',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        maskedQuoteId: window.checkoutConfig.quoteData.entity_id // send masked quote id to controller
                    },
                    showLoader: true,
                    success: function (res) {

                        // if memes data have items(memes url)
                        if (res && res.items && res.items.length) {
                            // set memes url list into observable array
                            self.memesList(res.items);
                            // set selected meme
                            self.selectedMeme(res.selected || null);
                        }
                    },
                    error: function (err) {
                        console.error('Ajax memes load error', err);
                    }
                });
            },

            saveSelectedMeme: function (memeUrl) {
                let self = this;
                self.selectedMeme(memeUrl);

                $.ajax({
                    url: '/memes/ajax/saveselectedmeme',
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true,
                    data: {
                        selected: memeUrl
                    },
                    success: function (res) {
                        if (res.success) {
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
