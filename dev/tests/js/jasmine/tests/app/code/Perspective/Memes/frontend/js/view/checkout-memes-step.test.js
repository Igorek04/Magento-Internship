define([
    'squire',
    'ko',
    'jquery',
    'underscore'
], function(Squire, ko, $, _) {
    'use strict';

    describe('Perspective_Memes/js/view/checkout-memes-step', function() {
        let injector, step, memePickerMock, stepNavigatorMock, container;
        beforeAll(function(done) {
            injector = new Squire();

            // create DOM container for React component
            container = document.createElement('div');
            container.id = 'react-meme-picker';
            document.body.appendChild(container);

            // create spies for meme picker and step navigator
            memePickerMock = jasmine.createSpy('memePickerInit');
            stepNavigatorMock = {
                registerStep: jasmine.createSpy('registerStep'),
                next: jasmine.createSpy('next')
            };

            // mock checkout config data
            window.checkoutConfig = {
                memesData: {
                    items: ['meme1.jpg', 'meme2.jpg'],
                    selected: 'meme1.jpg'
                },
                quoteData: {
                    entity_id: 123
                }
            };

            // mock modules using Squire
            injector.mock({
                'Perspective_Memes/js/view/meme-picker': memePickerMock,
                'Magento_Checkout/js/model/step-navigator': stepNavigatorMock,
                'ko': ko,
                'jquery': $,
                'underscore': _
            });

            // require step module and initialize it
            injector.require(['Perspective_Memes/js/view/checkout-memes-step'], function(CheckoutMemesStep) {
                    step = new CheckoutMemesStep(); // initialize step
                    done();
            });
        });

        // clean DOM and Squire injector
        afterAll(function() {
            if (container && container.parentNode) {
                container.parentNode.removeChild(container);
            }
            injector.clean();
            injector.remove();
        });

        it('initialize memes and register step if memes exist', function() {
            // check if memes data set in step
            expect(step.memesList().length).toBe(2);
            expect(step.selectedMeme()).toBe('meme1.jpg');
            // check if step registration called
            expect(stepNavigatorMock.registerStep).toHaveBeenCalled();
        });

        it('call memePickerInit when step becomes visible', function() {
            // toggle visibility to trigger subscription
            step.isVisible(false);
            step.isVisible(true);

            const element = document.getElementById('react-meme-picker');
            // check meme picker initialized with correct data
            expect(memePickerMock).toHaveBeenCalledWith(
                { memes: window.checkoutConfig.memesData, quoteId: 123 },
                element
            );
        });
    });
});
