define([
    'uiComponent',
    'ko',
    'Perspective_AsyncCatalog/js/model/catalog-data'
], function (Component, ko, catalogData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Perspective_AsyncCatalog/catalog/toolbar/renderer'
        },

        catalogData: catalogData,

        initialize: function () {
            var self = this;
            this._super();

            this.totalPages = ko.pureComputed(function () {
                var total = catalogData.totalCount();
                var size = catalogData.pageSize();
                return Math.ceil(total / size);
            });

            this.isFirstPage = ko.pureComputed(function () {
                return catalogData.currentPage() === 1;
            });

            this.isLastPage = ko.pureComputed(function () {
                return catalogData.currentPage() === self.totalPages();
            });

            this.getFramePages = ko.pureComputed(function () {
                var pages = [],
                    current = catalogData.currentPage(),
                    total = self.totalPages(),
                    delta = 2;

                if (total <= 0) return [];

                for (var i = 1; i <= total; i++) {
                    if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
                        pages.push({
                            number: i,
                            isCurrent: i === current,
                            type: 'page'
                        });
                    } else if (pages.length > 0 && pages[pages.length - 1].type !== 'jump') {
                        pages.push({ type: 'jump' });
                    }
                }
                return pages;
            });
        },

        getAmountText: function () {
            var total = catalogData.totalCount(),
                size = catalogData.pageSize(),
                currentPage = catalogData.currentPage(),
                first = ((currentPage - 1) * size) + 1,
                last = Math.min(currentPage * size, total);

            if (total > size) {
                return 'Items ' + first + '-' + last + ' of ' + total;
            }
            return total + (total === 1 ? ' Item' : ' Items');
        },

        previousPage: function () {
            if (!this.isFirstPage()) {
                this.changePage(catalogData.currentPage() - 1);
            }
        },

        nextPage: function () {
            if (!this.isLastPage()) {
                this.changePage(catalogData.currentPage() + 1);
            }
        },

        changePage: function (pageNumber) {
            catalogData.currentPage(pageNumber);
            catalogData.loadProducts();
        },

        changePageSize: function (size) {
            catalogData.pageSize(size);
            catalogData.currentPage(1);
            catalogData.loadProducts();
        },

        changeSort: function (field, direction) {
            if (field) {
                catalogData.currentSortField(field);
                catalogData.currentSortDirection(direction || 'asc');
                catalogData.currentPage(1);
                catalogData.loadProducts();
            }
        },

        changeDirection: function (data, event) {
            event.preventDefault();
            var currentDir = catalogData.currentSortDirection();
            var newDir = (currentDir === 'asc') ? 'desc' : 'asc';

            catalogData.currentSortDirection(newDir);
            catalogData.currentPage(1);
            catalogData.loadProducts();
        },

        getSortDirectionClass: function () {
            return 'sort-' + catalogData.currentSortDirection();
        },

        getSortDirectionTitle: function () {
            return catalogData.currentSortDirection() === 'asc'
                ? 'Set Descending Direction'
                : 'Set Ascending Direction';
        },

        changeMode: function (mode, event) {
            if (event) event.preventDefault();
            catalogData.currentMode(mode);
        }
    });
});
