define([
    'jquery',
    'ko',
    'uiComponent',
    'Perspective_AsyncCatalog/js/model/catalog-data',
    'mage/accordion'
], function ($, ko, Component, catalogData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Perspective_AsyncCatalog/catalog/filters/renderer',
            categoryId: null
        },

        aggregations: catalogData.aggregations,
        activeFilters: catalogData.activeFilters,

        initialize: function () {
            this._super();
            var self = this;

            if (this.categoryId) {
                catalogData.categoryId(this.categoryId);

                catalogData.loadFilters();
                catalogData.loadProducts();
            }

            this.aggregations.subscribe(function () {
                setTimeout(function () {
                    self.initAccordion($('#narrow-by-list'));
                    $('body').trigger('contentUpdated');
                }, 50);
            });

            if (this.aggregations().length) {
                this.initAccordion($('#narrow-by-list'));
            }

            return this;
        },

        removeFilter: function(filter) {
            catalogData.removeFilter(filter);
        },

        clearAll: function(data, event) {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }
            catalogData.clearAll();
        },

        initAccordion: function (element) {
            var $el = $(element);
            if (!$el.length || $el.find('[data-role=collapsible]').length === 0) {
                return;
            }


            try {
                if ($el.data('mage-accordion') || $el.data('mageAccordion')) {
                    $el.accordion('destroy');
                }
            } catch (e) {
                $el.removeData('mage-accordion').removeData('mageAccordion');
            }

            $el.accordion({
                "openedState": "active",
                "collapsible": true,
                "active": false,
                "multipleCollapsible": true,
                "header": "[data-role=collapsible] [data-role=title]",
                "content": "[data-role=collapsible] [data-role=content]",
                "trigger": "[data-role=collapsible] [data-role=title]"
            });

            $el.trigger('contentUpdated');
        },

        getAggregations: function () {
            var self = this;
            var allAggregations = this.aggregations();
            var active = this.activeFilters();

            if (!allAggregations) {
                return [];
            }

            var filteredData = allAggregations.filter(function (aggregation) {
                var isApplied = active.some(function (f) {
                    return f.code === aggregation.attribute_code;
                });

                return !isApplied;
            });

            filteredData.forEach(function (agg) {
                agg.options.forEach(function (opt) {
                    opt.boundApplyFilter = function (d, event) {
                        if (event) event.preventDefault();
                        catalogData.applyFilter(agg.attribute_code, opt.value, opt.label, agg.label);
                    };
                });
            });

            return filteredData;
        },
    });
});
