define(['async-catalog-core'], function (Core) {
    'use strict';

    Core.register('catalogToolbarSorter', (root, config) => ({

        init() {
        },

        changeSort() {
            root.catalogData.currentPage = 1;

            root.$nextTick(() => {
                root.catalogData.loadCatalog(false);
            });
        },

        changeDirection(event) {
            if (event) event.preventDefault();
            const current = root.catalogData.currentSortDirection;

            root.catalogData.currentSortDirection = current === 'asc' ? 'desc' : 'asc';

            root.catalogData.currentPage = 1;
            root.$nextTick(() => {
                root.catalogData.loadCatalog(false);
            });
        },

        getSortDirectionClass() {
            return 'sort-' + root.catalogData.currentSortDirection.toLowerCase();
        },

        getSortDirectionTitle() {
            return root.catalogData.currentSortDirection === 'asc'
                ? 'Set Descending Direction'
                : 'Set Ascending Direction';
        }


    }));
});
