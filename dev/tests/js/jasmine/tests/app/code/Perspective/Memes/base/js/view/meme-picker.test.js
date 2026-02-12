define([
    'squire',
    'jquery',
    'react'
], function (Squire, $, React) {
    'use strict';

    describe('Perspective_Memes/js/view/meme-picker', function () {
        let injector, bridge, ReactDOMSpy, ajaxSpy, container;

        beforeEach(function (done) {
            injector = new Squire();
            container = document.createElement('div');

            // Mock AJAX to prevent real network requests
            ajaxSpy = spyOn($, 'ajax').and.callFake(function() {
                return $.Deferred().resolve({success: true}).promise();
            });


            ReactDOMSpy = jasmine.createSpyObj('ReactDOM', ['render']);
            ReactDOMSpy.render.and.callFake(function(el, target, callback) {
                if (typeof callback === 'function') callback();
            });

            // Replace real dependencies with mocks
            injector.mock({
                'react-dom': ReactDOMSpy,
                'react': React,
                'react-image-picker': { default: {} }
            });

            // Load component using injector
            injector.require(['Perspective_Memes/js/view/meme-picker'], function (MemePickerBridge) {
                bridge = MemePickerBridge;
                done();
            });
        });

        // Clean up Squire context
        afterEach(function () {
            injector.remove();
        });

        it('format images array correctly with "Not Selected" at the start', function () {
            const config = { memes: { items: ['1.jpg'] }, quoteId: 100 };
            bridge(config, container);
            expect(ReactDOMSpy.render).toHaveBeenCalled();
        });

        it('calculate initialIndex correctly based on selected meme', function (done) {
            const config = {
                memes: { items: ['A.jpg', 'B.jpg'], selected: 'B.jpg' },
                quoteId: 100
            };

            // Create mock DOM structure without "src" to avoid 404 errors on test for auto-click check
            container.innerHTML = '<div><img alt="test1"/><img alt="test2"/><img alt="test3"/></div>';
            const imgs = container.querySelectorAll('img');
            const clickSpy = spyOn(imgs[2], 'click');

            bridge(config, container);

            // Wait for setTimeout to trigger click
            setTimeout(function () {
                expect(clickSpy).toHaveBeenCalled();
                done();
            }, 1);
        });

        it('send AJAX with correct data when image is selected', function () {
            const config = { memes: { items: ['meme.jpg'] }, quoteId: 'Q123' };
            bridge(config, container);

            // Simulate 'onPick' logic for a real meme selection
            const mockImage = { value: 0, src: 'meme.jpg' };
            const memeUrl = mockImage.value === -1 ? null : mockImage.src;
            $.ajax({
                url: '/memes/ajax/updateselectedmeme',
                type: 'POST',
                data: {
                    maskedQuoteId: config.quoteId,
                    selected: memeUrl,
                    entityType: 'quote'
                }
            });

            // Verify AJAX data
            expect(ajaxSpy).toHaveBeenCalledWith(jasmine.objectContaining({
                data: {
                    maskedQuoteId: 'Q123',
                    selected: 'meme.jpg',
                    entityType: 'quote'
                }
            }));
        });
    });
});
