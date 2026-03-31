define(['async-catalog-core'], function (Core) {
    'use strict';

    Core.register('catalogFiltersQuery', (root, config) => ({

        init() {

        },

        getPreparedFilters() {
            let filterInput = {
                category_uid: { eq: String(config.categoryConfig.categoryUid) }
            };

            root.catalogFiltersData.activeFilters.forEach(f => {
                if (f.code === 'price') {
                    const [from, to] = f.value.split('_');
                    filterInput.price = {
                        from: String(from),
                        to: String(to)
                    };
                } else if (f.code === 'category_uid') {
                    filterInput.category_uid = { eq: String(f.value) };
                } else {
                    if (!filterInput[f.code]) {
                        filterInput[f.code] = { in: [] };
                    }
                    filterInput[f.code].in.push(String(f.value));
                }
            });

            return filterInput;
        }

    }));
});
