define(['async-catalog-core'], function (Core) {
    'use strict';

    /**
     * @param {Object} root   - Alpine (root.products, root.config etc)
     * @param {Object} config - Config from phtml Core.create({ ... })
     */
    Core.register('componentName', (root, config) => ({
        // 1. Data
        testik: [],
        isTest: false,

        // 2. Initialize component
        init() {
            console.log('[Module: componentName] Ready');
        },

        // 3. Actions
        getData() {
            this.isTest = true;
            console.log('Accessing total from products:', root.products.total);
        }
    }));
});
