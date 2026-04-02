define(['async-catalog-core'], function (Core) {
    'use strict';

    Core.register('catalogLazyload', (root, config) => ({
        init() {},

        lazyLoadNextPage() {
            if (root.catalogData.isLoading) return;

            if (root.catalogData.currentPage < root.catalogToolbarPager.getTotalPages()) {
                root.catalogData.currentPage++;
                root.catalogData.loadCatalog(true);
            }
        }
    }));
});
