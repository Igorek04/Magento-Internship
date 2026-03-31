define(['async-catalog-core', 'jquery', 'mage/accordion'], function (Core, $) {
    'use strict';

    Core.register('catalogFiltersUI', (root, config) => ({

        enrichedDisplayFilters: [],

        init() {
            root.$watch('catalogData.aggregations', (newAgg) => {
                if (newAgg && newAgg.length > 0) {
                    this.updateAggregations();

                    root.$nextTick(() => {
                        this.initAccordion();
                    });
                }
            });

            root.$watch('catalogFiltersData.stagedFilters', () => {
                this.enrichDisplayFilters();
                this.updateAggregations();
            });
        },

        updateAggregations() {
            const filteredAggs = root.catalogData.aggregations.filter(agg =>
                !root.catalogFiltersData.stagedFilters.some(f => f.code === agg.attribute_code) &&
                !(agg.attribute_code === 'category_uid' && !config.categoryConfig.hasChildCategories)
            );

            root.catalogFiltersData.aggregations = filteredAggs;
        },

        initAccordion() {
            const $el = $('#narrow-by-list');

            if (!$el.length || $el.find('[data-role=collapsible]').length === 0) {
                return;
            }

            if ($el.data('mage-accordion') || $el.data('mageAccordion')) {
                $el.accordion('destroy');
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

            $('body').trigger('contentUpdated');
        },

        enrichDisplayFilters() {
            this.enrichedDisplayFilters = root.catalogFiltersData.stagedFilters.map(f => {
                if (f.label && f.attrLabel) return f;

                const agg = root.catalogData.aggregations.find(a => a.attribute_code === f.code);
                const opt = agg?.options.find(o => String(o.value) === String(f.value));

                return opt ? { ...f, label: opt.label, attrLabel: agg.label } : f;
            });
        },

    }));
});
