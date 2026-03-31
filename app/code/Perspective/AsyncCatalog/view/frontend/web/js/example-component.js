define(['async-catalog-core'], function (Core) {
    'use strict';

    /**
     * @param {Object} root   - Alpine (root.products, root.config etc)
     * @param {Object} config - ConfcatalogCore.create({ ... }) в PHTML
     */
    Core.register('componentName', (root, config) => ({
        // 1. Свойства (Data)
        items: [],
        isLoading: false,

        // 2. Инициализация (Вызывается ядром автоматически)
        init() {
            // Здесь можно запустить первичную загрузку или подписаться на события
            console.log('[Module: componentName] Ready');
        },

        // 3. Методы (Actions)
        getData() {
            this.isLoading = true;
            // Логика модуля...
            console.log('Accessing total from products:', root.products.total);
        }
    }));
});
