define(['async-catalog-core', 'jquery', 'mage/loader'], function (Core, $) {
    'use strict';

    Core.register('catalogLoader', (root, config) => ({

        init() {
            const $container = $('.async-catalog-root');

            if (!$container.length) {
                console.warn('[Loader] Container not found');
                return;
            }

            $container.loader({
                icon: config.loaderIcon
            });

            // loader on first load
            if (root.catalogData.isLoading) {
                $container.loader('show');
            }

            root.$watch('catalogData.isLoading', (value) => {
                if (value) {
                    $container.loader('show');
                } else {
                    $container.loader('hide');
                }
            });
        }

    }));
});
