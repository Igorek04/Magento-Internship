define(['ko', 'jquery'], function (ko, $) {
    'use strict';
    return {
        products: ko.observableArray([]),
        aggregations: ko.observableArray([]),
        isLoading: ko.observable(false),
        categoryId: ko.observable(null),

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
            this.loadProducts();
            this.loadFilters();
        },

        clearAll: function() {
            this.activeFilters([]);
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

            var query = `query GetProducts($filter: ProductAttributeFilterInput) {
                products(filter: $filter, pageSize: 12) {
                    items {
                      id
                      name
                      sku
                      url_key
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
                    variables: { filter: this.getPreparedFilters() }
                }),
                success: function (res) {
                    if (res.data && res.data.products) {
                        self.products(res.data.products.items);
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
                this.loadProducts();
                this.loadFilters();
            } catch (err) {
                console.error("Ошибка в applyFilter:", err);
            }
        }

    };
});
