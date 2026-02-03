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

            initialize: function () {
                this._super();
                stepNavigator.registerStep(
                    this.stepCode,
                    null,
                    this.stepTitle,
                    this.isVisible,
                    _.bind(this.navigate, this),
                    15
                );
                //test post
                this.testPostAjax();

                return this;
            },

            navigate: function () {},

            navigateToNextStep: function () { stepNavigator.next(); },

            testPostAjax: function () {
                var self = this;
                $.ajax({
                    url: '/memes/ajax/test',
                    type: 'POST',
                    dataType: 'json',
                    data: { test: 'Hello World!' },
                    showLoader: true,
                    success: function (res) {
                        //self.isVisible(false); -
                        console.log('POST response:', res);
                        if (res.success) {
                            alert(' POST success. Message: ' + res.message);
                        }
                    },
                    error: function (err) {
                        console.error('POST AJAX error', err);
                    }
                });
            }
        });
    });
