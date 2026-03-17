define(['ko', 'jquery', 'mage/accordion'], function (ko, $) {
    'use strict';
    return {
        products: ko.observableArray([]),
        aggregations: ko.observableArray([]),
        isLoading: ko.observable(false),
        categoryId: ko.observable('4'),

        activeFilters: ko.observable({}),

        applyFilter: function (code, value, event) {
            if (event) event.preventDefault();

            console.log('Фильтруем по:', code, value);

            var filterObject = {};
            filterObject[code] = { eq: String(value) };

            this.loadProducts(filterObject);
        },

        loadFilters: function () {
            var self = this;
            var query = `query GetFilters($id: String!) {
                products(filter: {category_id: {eq: $id}}) {
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
                    variables: { id: this.categoryId() }
                }),
                success: function (res) {
                    if (res.data) {
                        self.aggregations(res.data.products.aggregations);
                        console.log('filters', res);

                        setTimeout(function() {
                            var $list = $('#narrow-by-list');
                            if ($list.length) {
                                $list.accordion({
                                    "openedState": "active",
                                    "collapsible": true,
                                    "active": false,
                                    "multipleCollapsible": true
                                });
                            }
                        }, 200);
                    }
                }
            });
        },

        loadProducts: function (appliedFilters = {}) {
            var self = this;
            this.isLoading(true);

            var filterInput = Object.assign({
                category_id: { eq: this.categoryId() }
            }, appliedFilters);

            var query = `query GetProducts($filter: ProductAttributeFilterInput) {
                products(filter: $filter, pageSize: 12) {
                    items {
                      id
                      name
                      sku
                      url_key
                      stock_status
                      small_image { url label }
                      price_range {
                        minimum_price {
                          final_price { value currency }
                        }
                      }
                    }
                }
            }`;

            $.ajax({
                url: '/graphql',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    query: query,
                    variables: { filter: filterInput }
                }),
                success: function (res) {
                    if (res.data) {
                        self.products(res.data.products.items);
                        console.log('products', res);
                    }
                },
                complete: function () { self.isLoading(false); }
            });
        }
    };
});
