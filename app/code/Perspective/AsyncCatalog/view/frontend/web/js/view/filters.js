define([
    'ko',
    'uiComponent',
    'Perspective_AsyncCatalog/js/model/catalog-data'
], function (ko, Component, catalogData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Perspective_AsyncCatalog/catalog/filters',
        },

        aggregations: catalogData.aggregations,

        applyFilter: function (code, value, event) {
            return catalogData.applyFilter(code, value, event);
        },

        getAggregations: function () {
            var self = this;
            var data = this.aggregations();

            if (data) {
                data.forEach(function (item) {
                    item.boundApplyFilter = self.applyFilter.bind(self);
                });
            }
            return data;
        }
    });
});
