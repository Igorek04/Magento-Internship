define(['async-catalog-core'], function (Core) {
    'use strict';

    Core.register('catalogFiltersActions', (root, config) => ({

        init() {
            window.addEventListener('filter-apply', (e) => {
                this.handleFilterClick(e.detail);
            });

            window.addEventListener('filter-submit', () => {
                this.applyStagedFilters();
            });

            window.addEventListener('filters-clear', () => {
                this.handleClearAll();
            });
        },

        handleFilterClick(detail) {
            const { code, value, label, attrLabel } = detail;

            const exists = root.catalogFiltersData.stagedFilters.find(
                f => f.code === code && f.value === value
            );

            if (exists) {
                root.catalogFiltersData.stagedFilters =
                    root.catalogFiltersData.stagedFilters.filter(f => f !== exists);
            } else {
                root.catalogFiltersData.stagedFilters.push({ code, value, label, attrLabel });
            }

            const isAutoUpdate = parseInt(root.catalogData.moduleConfig?.filtrationMode || 0);

            if (isAutoUpdate) {
                root.catalogFiltersData.activeFilters = [...root.catalogFiltersData.stagedFilters];
                root.catalogFiltersData.hasChanges = false;
                root.catalogData.currentPage = 1;
                root.catalogData.activeFilters = root.catalogFiltersData.activeFilters;
                root.catalogData.loadCatalog(false);
            } else {
                root.catalogFiltersData.hasChanges =
                    JSON.stringify(root.catalogFiltersData.stagedFilters) !==
                    JSON.stringify(root.catalogFiltersData.activeFilters);
            }
        },

        applyStagedFilters() {
            root.catalogFiltersData.activeFilters = [...root.catalogFiltersData.stagedFilters];
            root.catalogFiltersData.hasChanges = false;
            root.catalogData.currentPage = 1;
            root.catalogData.activeFilters = root.catalogFiltersData.activeFilters;
            root.catalogData.loadCatalog(false);
        },

        handleClearAll() {
            root.catalogFiltersData.stagedFilters = [];

            const isAutoUpdate = parseInt(root.catalogData.moduleConfig?.filtrationMode || 0);

            if (isAutoUpdate) {
                root.catalogFiltersData.activeFilters = [];
                root.catalogFiltersData.hasChanges = false;
                root.catalogData.currentPage = 1;
                root.catalogData.activeFilters = [];
                root.catalogData.loadCatalog(false);
            } else {
                root.catalogFiltersData.hasChanges = root.catalogFiltersData.activeFilters.length > 0;
            }
        }

    }));
});
