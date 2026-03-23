define(['ko', 'jquery'], function (ko, $) {
    'use strict';
    return {
        products: ko.observableArray([]),
        aggregations: ko.observableArray([]),

        categoryId: ko.observable(null),
        totalCount: ko.observable(0),

        isLoading: ko.observable(false),

        pageSize: ko.observable(12),
        currentPage: ko.observable(1),
        availablePages: ko.observableArray([]),

        currentSortField: ko.observable(''),
        currentSortDirection: ko.observable('asc'),
        availableSortList: ko.observableArray([]),

        availableModes: ko.observableArray([]),
        currentMode: ko.observable('grid'),

        activeFilters: ko.observableArray([]),


        getPreparedFilters: function() {
            var filterInput = { category_id: { eq: this.categoryId() } };
            this.activeFilters().forEach(function(f) {
                if (!filterInput[f.code]) {
                    filterInput[f.code] = { in: [] };
                }
                filterInput[f.code].in.push(String(f.value));
            });
            return filterInput;
        },



        removeFilter: function(filter) {
            this.activeFilters.remove(filter);
            this.currentPage(1)
            this.loadProducts();
            this.loadFilters();
        },

        clearAll: function() {
            this.activeFilters([]);
            this.currentPage(1)
            this.loadProducts();
            this.loadFilters();
        },

        loadFilters: function () {
            var self = this;
            var query = `query GetFilters($filter: ProductAttributeFilterInput) {
                products(filter: $filter) {
                    aggregations {
                        label
                        attribute_code
                        options { label value count }
                    }
                }
            }`;
            $.ajax({
                url: '/graphql',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    query: query,
                    variables: { filter: this.getPreparedFilters() }
                }),
                success: function (res) {
                    if (res.data && res.data.products) {
                        self.aggregations(res.data.products.aggregations);
                    }
                }
            });
        },

        loadProducts: function () {
            var self = this;
            this.isLoading(true);

            var query = `query GetProducts($filter: ProductAttributeFilterInput, $pageSize: Int, $currentPage: Int, $sort: ProductAttributeSortInput) {
                products(filter: $filter, pageSize: $pageSize, currentPage: $currentPage, sort: $sort) {
                    total_count
                    items {
                      id
                      name
                      sku
                      url_rewrites {
                         url
                      }
                      stock_status
                      rating_summary
                      review_count
                      small_image { url label }
                      price_range {
                        minimum_price {
                          final_price { value currency }
                        }
                      }
                    }
                }
            }`;

            return $.ajax({
                url: '/graphql',
                method: 'POST',
                global: false,
                contentType: 'application/json',
                data: JSON.stringify({
                    query: query,
                    variables: {
                        filter: this.getPreparedFilters(),
                        pageSize: parseInt(this.pageSize()),
                        currentPage: parseInt(this.currentPage()),
                        sort: { [this.currentSortField() || 'position']: this.currentSortDirection().toUpperCase() }
                    }
                }),
                success: function (res) {
                    if (res.data && res.data.products) {
                        var baseUrl = window.BASE_URL.replace(/\/$/, '');
                        var processedItems = res.data.products.items.map(function (item) {
                            item.url = baseUrl + '/' + item.url_rewrites[0].url;

                            item.isRendered = ko.observable(false);

                            return item;
                        });
                        self.products(processedItems);

                        //self.products(res.data.products.items);
                        self.totalCount(res.data.products.total_count);
                    }
                },
                complete: function () { self.isLoading(false); }
            });
        },

        applyFilter: function (code, value, label, attrLabel) {
            try {
                var exists = this.activeFilters().find(f => f.code === code && f.value === value);
                if (exists) {
                    this.activeFilters.remove(exists);
                } else {
                    this.activeFilters.push({
                        code: code,
                        value: value,
                        label: label,
                        attrLabel: attrLabel
                    });
                }
                this.currentPage(1)

                this.loadProducts();
                this.loadFilters();
            } catch (err) {
                console.error("Ошибка в applyFilter:", err);
            }
        }

    };
});
