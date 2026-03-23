define([
    'alpine',
    'jquery',
    'Magento_Catalog/js/price-utils',
    'Magento_Customer/js/customer-data',
    'Magento_Catalog/js/catalog-add-to-cart',
    'mage/accordion'
], function (Alpine, $, priceUtils, customerData) {
    'use strict';

    return function (config) {
        Alpine.data('catalogProvider', () => ({
            products: [],
            isLoading: false,
            categoryId: config.categoryId,
            pageConfig: config.pageConfig,

            currentMode: config.pageConfig.currentMode,

            currentSortField: config.pageConfig.defaultSortBy || 'position',
            currentSortDirection: 'asc',

            totalCount: 0,

            pageSize: 12,
            availablePages: [12],
            currentPage: 1,

            aggregations: [],
            activeFilters: [],

            init() {
                this.pageSize = this.pageConfig[`${this.currentMode}PerPageDefault`];

                this.loadCatalog();

                this.$watch('products', () => {
                    this.reinitCart();
                });

                this.$watch('currentMode', (newMode) => {
                    this.pageSize = this.pageConfig[`${newMode}PerPageDefault`];
                    this.currentPage = 1;
                    this.loadCatalog();
                });
            },

            formatPrice(priceData) {
                if (!priceData || priceData.value === undefined) {
                    return '';
                }

                const format = {
                    decimalSymbol: '.',
                    groupSymbol: ',',
                    precision: 2,
                    integerRequired: false,
                    pattern: priceData.currency === 'USD' ? '$%s' : '%s ' + priceData.currency
                };

                return priceUtils.formatPrice(priceData.value, format);
            },



            getFormKey() {
                return $.cookie('form_key');
            },

            getUenc() {
                return btoa(window.location.href)
                    .replace(/\+/g, '-')
                    .replace(/\//g, '_')
                    .replace(/=+$/, '');
            },

            getAddToCartUrl(productId) {
                return window.BASE_URL + 'checkout/cart/add/uenc/' + this.getUenc() + '/product/' + productId + '/';
            },

            reinitCart() {
                this.$nextTick(() => {
                    $('form[data-role="tocart-form"]').each(function () {
                        const $form = $(this);
                        if (!$form.data('mage-catalog-add-to-cart')) {
                            $form.catalogAddToCart();
                        }
                    });
                    $('body').trigger('contentUpdated');
                });
            },

            addToWishlist(productId) {
                const url = window.BASE_URL + 'wishlist/index/add/product/' + productId + '/';
                this.postAction(url, { product: productId });
            },

            addToCompare(productId) {
                const url = window.BASE_URL + 'catalog/product_compare/add/product/' + productId + '/';
                this.postAction(url, { product: productId });
            },

            postAction(url, data) {
                const self = this;
                $.ajax({
                    url: url,
                    data: Object.assign(data, {
                        'uenc': self.getUenc(),
                        'form_key': self.getFormKey()
                    }),
                    type: 'post',
                    showLoader: true,
                    success: (res) => {
                        if (res.backUrl && res.backUrl.includes('customer/account/login')) {
                            window.location.assign(res.backUrl);
                            return;
                        }

                        const sections = ['wishlist', 'compare-products', 'messages'];
                        customerData.invalidate(sections);
                        customerData.reload(sections, true);
                    }
                });
            },

            getAmountText() {
                const total = parseInt(this.totalCount);
                const size = parseInt(this.pageSize);
                const current = parseInt(this.currentPage);

                if (total === 0) return '';

                const first = ((current - 1) * size) + 1;
                const last = Math.min(current * size, total);

                if (total > size) {
                    return `Items ${first}-${last} of ${total}`;
                }

                return total === 1 ? `1 Item` : `${total} Items`;
            },

            changePageSize() {
                this.currentPage = 1;
                this.loadCatalog();
            },

            getAvailablePages() { //sizes
                const key = `${this.currentMode}PerPageValues`;
                return this.pageConfig[key] || [];
            },

            getTotalPages() {
                return Math.ceil(this.totalCount / this.pageSize) || 0;
            },

            getFramePages() {
                const total = this.getTotalPages();
                if (total <= 1) return [];
                const current = parseInt(this.currentPage);
                const delta = 2;
                let pages = [];

                for (let i = 1; i <= total; i++) {
                    if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
                        pages.push({ number: i, isCurrent: i === current, type: 'page' });
                    } else if (pages.length > 0 && pages[pages.length - 1].type !== 'jump') {
                        pages.push({ type: 'jump' });
                    }
                }
                return pages;
            },

            changePage(pageNumber) {
                this.currentPage = parseInt(pageNumber);
                this.loadCatalog();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            changeSort() {
                this.currentPage = 1;
                this.loadCatalog();
            },

            changeDirection(event) {
                if (event) event.preventDefault();
                this.currentSortDirection = (this.currentSortDirection === 'asc') ? 'desc' : 'asc';
                //this.currentPage = 1;
                this.loadCatalog();
            },

            getSortDirectionClass() {
                return 'sort-' + this.currentSortDirection;
            },

            getSortDirectionTitle() {
                return this.currentSortDirection === 'asc'
                    ? 'Set Descending Direction'
                    : 'Set Ascending Direction';
            },

            changeMode(mode, event) {
                if (event) event.preventDefault();
                this.currentMode = mode;
            },

            getPreparedFilters() {
                let filterInput = { category_id: { eq: String(this.categoryId) } };
                this.activeFilters.forEach(f => {
                    if (!filterInput[f.code]) {
                        filterInput[f.code] = { in: [] };
                    }
                    filterInput[f.code].in.push(String(f.value));
                });
                return filterInput;
            },

            loadCatalog() {
                const self = this;
                this.isLoading = true;

                const query = `query GetCatalog($filter: ProductAttributeFilterInput, $pageSize: Int, $currentPage: Int, $sort: ProductAttributeSortInput) {
                                        products(filter: $filter, pageSize: $pageSize, currentPage: $currentPage, sort: $sort) {
                                            total_count
                                            items {
                                                id
                                                name
                                                sku
                                                url_rewrites { url }
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
                                            aggregations {
                                                label
                                                attribute_code
                                                options { label value count }
                                            }
                                        }
                                    }`;

                return $.ajax({
                    url: '/graphql',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        query: query,
                        variables: {
                            filter: this.getPreparedFilters(),
                            pageSize: parseInt(this.pageSize),
                            currentPage: parseInt(this.currentPage),
                            sort: { [this.currentSortField]: this.currentSortDirection.toUpperCase() }
                        }
                    }),
                    success: function (res) {
                        if (res.data && res.data.products) {
                            const data = res.data.products;
                            const baseUrl = (window.BASE_URL || '').replace(/\/$/, '');

                            self.totalCount = data.total_count;
                            self.products = data.items.map(item => {
                                item.url = item.url_rewrites?.[0] ? baseUrl + '/' + item.url_rewrites[0].url : '#';
                                return item;
                            });

                            self.aggregations = data.aggregations;

                            window.dispatchEvent(new CustomEvent('catalog-filters-updated', {
                                detail: {
                                    aggregations: self.aggregations.filter(agg =>
                                        !self.activeFilters.some(f => f.code === agg.attribute_code)
                                    ),
                                    activeFilters: self.activeFilters
                                }
                            }));
                        }
                    },
                    complete: function () {
                        self.isLoading = false;
                    }
                });
            },

            handleFilterClick(detail) {
                const { code, value, label, attrLabel } = detail;
                const exists = this.activeFilters.find(f => f.code === code && f.value === value);

                if (exists) {
                    this.activeFilters = this.activeFilters.filter(f => f !== exists);
                } else {
                    this.activeFilters.push({ code, value, label, attrLabel });
                }

                const filteredAggs = this.aggregations.filter(agg =>
                    !this.activeFilters.some(f => f.code === agg.attribute_code)
                );

                window.dispatchEvent(new CustomEvent('catalog-filters-updated', {
                    detail: {
                        aggregations: filteredAggs,
                        activeFilters: this.activeFilters
                    }
                }));

                this.currentPage = 1;
                this.loadCatalog();
            },

            initAccordion() {
                this.$nextTick(() => {
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

                });
            },

            handleClearAll() {
                this.activeFilters = [];
                this.currentPage = 1;

                window.dispatchEvent(new CustomEvent('catalog-filters-updated', {
                    detail: {
                        aggregations: this.aggregations,
                        activeFilters: []
                    }
                }));

                this.loadCatalog();
            },
        }));

        if (!window.Alpine.initialized) {
            Alpine.start();
            window.Alpine.initialized = true;
        }
    };
});
