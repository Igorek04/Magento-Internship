define([
    'Perspective_Memes/js/view/order-history-memes'
], function(OrderHistoryMemes) {
    'use strict';

    describe('Perspective_Memes/js/view/order-history-memes', function () {
        let container;

        // Create container div before each test
        beforeEach(function () {
            container = document.createElement('div');
            container.id = 'meme-container';
            document.body.appendChild(container);
        });

        // Clean up container after each test
        afterEach(function () {
            document.body.removeChild(container);
            container = null;
        });

        it('show "Not Found" when memes data is missing', function () {
            OrderHistoryMemes({}, container);
            expect(container.textContent).toBe('Not Found');
        });

        it('show "Not Selected" when memes.selected is empty', function () {
            OrderHistoryMemes({ memes: { selected: null } }, container);
            expect(container.textContent).toBe('Not Selected');
        });

        it('render img when memes.selected is present', function () {
            let memeUrl = 'https://example.com/meme.jpg';
            OrderHistoryMemes({ memes: { selected: memeUrl } }, container);

            let img = container.querySelector('img');
            expect(img).not.toBeNull();           // Check image exists
            expect(img.src).toBe(memeUrl);       // Check correct src
        });

        it('open and close modal when image and modal are clicked', function () {
            let memeUrl = 'https://example.com/meme.jpg';
            OrderHistoryMemes({ memes: { selected: memeUrl } }, container);

            let img = container.querySelector('img');
            expect(img).not.toBeNull();

            // Simulate click on mini-image - modal should open
            img.click();

            let modal = container.querySelector('div'); // modal div
            expect(modal).not.toBeNull();

            // Check that modal contains the full-size img
            let fullImg = modal.querySelector('img');
            expect(fullImg).not.toBeNull();
            expect(fullImg.src).toBe(memeUrl);

            // Simulate click on modal - modal should close
            modal.click();

            // After click, modal should be removed from DOM
            let closedModal = container.querySelector('div');
            expect(closedModal).toBeNull();
        });
    });
});
