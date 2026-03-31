define(['async-catalog-core'], function (Core) {
    'use strict';

    Core.register('catalogFiltersUrl', (root, config) => ({

        init() {
            this.parseUrlToFilters();

            root.$watch('catalogData.isLoading', (isLoading) => {
                if (!isLoading) {
                    this.updateUrl();
                }
            });

            window.onpopstate = () => {
                this.parseUrlToFilters();
                root.catalogData.currentPage = 1;
                root.catalogData.loadCatalog(false);
            };
        },

        parseUrlToFilters() {
            const params = new URLSearchParams(window.location.search);
            const newFilters = [];

            params.forEach((value, code) => {
                value.split(',').forEach(val => {
                    newFilters.push({
                        code: code,
                        value: val,
                        label: '',
                        attrLabel: ''
                    });
                });
            });

            root.catalogFiltersData.activeFilters = newFilters;
            root.catalogFiltersData.stagedFilters = [...newFilters];
            root.catalogFiltersData.hasChanges = false;

            root.catalogData.activeFilters = newFilters;
        },

        updateUrl() {
            const params = new URLSearchParams();

            root.catalogFiltersData.activeFilters.forEach(f => {
                const current = params.get(f.code);
                params.set(f.code, current ? `${current},${f.value}` : f.value);
            });

            const queryString = params.toString();
            const newUrl = window.location.pathname + (queryString ? '?' + queryString : '');

            if (window.location.search !== (queryString ? '?' + queryString : '')) {
                window.history.pushState(null, '', newUrl);
            }
        }

    }));
});
