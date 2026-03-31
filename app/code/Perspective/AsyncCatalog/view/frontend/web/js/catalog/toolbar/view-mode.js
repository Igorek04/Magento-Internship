define(['async-catalog-core'], function (Core) {
    'use strict';

    Core.register('catalogToolbarViewmode', (root, config) => ({
        currentMode: config.pageConfig.currentMode || 'grid',

        init() {

        },

        changeMode(mode, event) {
            if (event) event.preventDefault();

            this.currentMode = mode;

            root.$nextTick(() => {
                const key = `${mode}PerPageDefault`;
                root.catalogData.pageSize = parseInt(root.catalogData.pageConfig[key]);
                root.catalogData.currentPage = 1;
                root.catalogData.loadCatalog(false);
            });
        }
    }));
});
