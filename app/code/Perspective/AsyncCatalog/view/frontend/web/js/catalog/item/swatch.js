define(['async-catalog-core', 'jquery'], function (Core, $) {
    'use strict';

    Core.register('catalogSwatches', (root, config) => ({

        swatchCache: {},

        init() {
            $(document).ajaxComplete((event, xhr, settings) => {
                if (!settings.url.includes('/swatches/ajax/media/')) return;

                const urlParams = new URLSearchParams(settings.url.split('?')[1]);
                const simpleId = urlParams.get('product_id');

                try {
                    const res = JSON.parse(xhr.responseText);
                    const newUrl = res.medium;

                    if (!simpleId || !newUrl) return;

                    let confId = null;
                    $('.swatch-holder').each(function () {
                        const widget = $(this).find('[class^="swatch-opt-"]').data('mage-SwatchRenderer')
                            || $(this).find('[class^="swatch-opt-"]').data('mageSwatchRenderer');

                        if (widget && widget.getProduct() == simpleId) {
                            confId = $(this).attr('data-id');
                            return false;
                        }
                    });

                    if (confId) {
                        if (!this.swatchCache[confId]) this.swatchCache[confId] = {};
                        this.swatchCache[confId][simpleId] = newUrl;

                        const product = root.catalogData.products.find(p => p.id == confId);
                        if (product) {
                            product.small_image.url = newUrl;
                        }
                    }
                } catch (e) {
                    console.error('[Swatches] Error parsing response:', e);
                }
            });

            $(document).on('click', '.swatch-option', (e) => {
                const $container = $(e.currentTarget).closest('.swatch-holder');
                const confId = $container.attr('data-id');
                const product = root.catalogData.products.find(p => p.id == confId);

                if (!product) return;

                if (!product._baseImage) {
                    product._baseImage = product.small_image.url;
                }

                const widget = $container.find('[class^="swatch-opt-"]').data('mage-SwatchRenderer')
                    || $container.find('[class^="swatch-opt-"]').data('mageSwatchRenderer');

                const currentSimpleId = widget ? widget.getProduct() : null;

                if (!currentSimpleId) {
                    product.small_image.url = product._baseImage;
                } else if (this.swatchCache[confId] && this.swatchCache[confId][currentSimpleId]) {
                    product.small_image.url = this.swatchCache[confId][currentSimpleId];
                }
            });
        }
    }));
});
