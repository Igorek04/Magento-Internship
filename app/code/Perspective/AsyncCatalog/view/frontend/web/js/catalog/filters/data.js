define(['async-catalog-core'], function (Core) {
    'use strict';

    Core.register('catalogFiltersData', (root, config) => ({
        aggregations: [],
        activeFilters: [],

        stagedFilters: [],
        hasChanges: false,

        init() {
            root.$watch('catalogData.isLoading', (isLoading) => {
                if (!isLoading) {
                    this.stagedFilters = [...this.activeFilters];
                }
            });
        },

        isFilterActive(code, value) {
            return this.activeFilters.some(f => f.code === code && f.value === value);
        },

        isFilterStaged(code, value) {
            return this.stagedFilters.some(f => f.code === code && f.value === value);
        }
    }));
});
