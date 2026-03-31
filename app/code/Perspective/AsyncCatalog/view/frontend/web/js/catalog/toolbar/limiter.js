define(['async-catalog-core'], function (Core) {
    'use strict';

    Core.register('catalogToolbarLimiter', (root, config) => ({

        init() {

        },

        changePageSize() {
            root.catalogData.currentPage = 1;
            root.catalogData.loadCatalog(false);
        },

        getAvailablePages() {
            const mode = root.catalogToolbarViewmode.currentMode;
            const key = `${mode}PerPageValues`;
            return root.catalogData.pageConfig[key] || [];
        }

    }));
});
