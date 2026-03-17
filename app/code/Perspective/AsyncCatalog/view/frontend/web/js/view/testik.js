define([
    'uiComponent',
    'ko',
    'jquery'
], function (Component, ko, $) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Perspective_AsyncCatalog/testik',
            products: ko.observableArray([]),
            isLoading: ko.observable(false)
        },

        initialize: function () {
            this._super();
            this.loadCatalog();
        },

        loadCatalog: function () {
            var self = this;
            this.isLoading(true);

            var query = `
                query getProducts($id: String!) {
                    products(filter: {category_id: {eq: $id}}, pageSize: 12) {
                        items {
                            name
                            sku
                        }
                    }
                }
            `;

            $.ajax({
                url: '/graphql',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    query: query,
                    variables: {
                        id: "3"
                    }
                }),
                success: function (response) {
                    if (response.data && response.data.products) {
                        self.products(response.data.products.items);
                        console.log(response);
                    }
                },
                error: function (error) {
                    console.error('GraphQL Error:', error);
                },
                complete: function () {
                    self.isLoading(false);
                }
            });
        }
    });
});
