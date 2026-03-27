define([
    'alpine',
    'alpine-intersect',
    'jquery',
    'Magento_Catalog/js/price-utils',
    'Magento_Customer/js/customer-data',
    'Magento_Catalog/js/catalog-add-to-cart',
    'mage/accordion',
    'mage/loader',
], function (Alpine, alpineIntersect, $, priceUtils, customerData) {
    'use strict';

    if (typeof Alpine.plugin === 'function') {
        Alpine.plugin(alpineIntersect);
    }

    return function (config) {
        Alpine.data('catalogProvider', () => ({
            products: [],
            isLoading: false,
            categoryId: config.categoryId,
            pageConfig: config.pageConfig,
            moduleConfig: config.moduleConfig,
            categoryConfig: config.categoryConfig,

            currentMode: config.pageConfig.currentMode,

            currentSortField: config.pageConfig.defaultSortBy || 'position',
            currentSortDirection: 'asc',

            totalCount: 0,

            pageSize: 12,
            availablePages: [12],
            currentPage: 1,

            aggregations: [],

            activeFilters: [],
            stagedFilters: [],

            hasChanges: false,

            currencyCode: config.currencyCode,

            init() {
                this.pageSize = this.pageConfig[`${this.currentMode}PerPageDefault`];

                this.parseUrlToFilters();

                window.formatPrice = this.formatPrice.bind(this);
                window.currencyCode = this.currencyCode;

                this.stagedFilters = [...this.activeFilters];

                const $container = $(this.$el);
                $container.loader({
                    icon: config.loaderIcon
                });

                this.$watch('isLoading', (value) => {
                    if (value) {
                        $container.loader('show');
                    } else {
                        $container.loader('hide');
                        this.updateUrl();
                    }
                });

                this.loadCatalog(false);

                this.$watch('products', () => {
                    this.reinitCart();
                });

                this.$watch('currentMode', (newMode) => {
                    this.pageSize = this.pageConfig[`${newMode}PerPageDefault`];
                    this.currentPage = 1;
                    this.loadCatalog(false);
                });

                // product swatches
                this.swatchCache = {};
                //on first swatch option click
                $(document).ajaxComplete((event, xhr, settings) => {
                    // filter of ajax requests
                    if (!settings.url.includes('/swatches/ajax/media/')) return;

                    const urlParams = new URLSearchParams(settings.url.split('?')[1]);
                    const simpleId = urlParams.get('product_id');
                    const res = JSON.parse(xhr.responseText);
                    const newUrl = res.medium;

                    if (!simpleId || !newUrl) return;

                    // search configurable product by simple from ajax response
                    let confId = null;
                    $('.swatch-holder').each(function() {
                        const widget = $(this).find('[class^="swatch-opt-"]').data('mage-SwatchRenderer')
                            || $(this).find('[class^="swatch-opt-"]').data('mageSwatchRenderer');
                        if (widget && widget.getProduct() == simpleId) {
                            confId = $(this).attr('data-id');
                            return false;
                        }
                    });

                    if (confId) {
                        if (!this.swatchCache[confId]) this.swatchCache[confId] = {};
                        this.swatchCache[confId][simpleId] = newUrl;

                        const product = this.products.find(p => p.id == confId);
                        // set option img url to product card
                        if (product) {
                            product.small_image.url = newUrl;
                        }
                    }
                });

                // on repeated swatch option click
                $(this.$el).on('click', '.swatch-option', (e) => {
                    const $container = $(e.currentTarget).closest('.swatch-holder');
                    const confId = $container.attr('data-id');
                    const product = this.products.find(p => p.id == confId);

                    // save base product img url
                    if (product && !product._baseImage) {
                        product._baseImage = product.small_image.url;
                    }

                    const widget = $container.find('[class^="swatch-opt-"]').data('mage-SwatchRenderer')
                        || $container.find('[class^="swatch-opt-"]').data('mageSwatchRenderer');

                    // get current selected simple product id from swatch renderer widget
                    const currentSimpleId = widget ? widget.getProduct() : null;

                    // if clean swatch selection - set base image
                    if (!currentSimpleId) {
                        product.small_image.url = product._baseImage;

                        // if user repeat selection of same options - get cached option img url
                    } else if (this.swatchCache[confId] && this.swatchCache[confId][currentSimpleId]) {
                        product.small_image.url = this.swatchCache[confId][currentSimpleId];
                    }
                });

                //
                window.onpopstate = () => {
                    this.parseUrlToFilters();
                    this.loadCatalog(false);
                };
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
                this.loadCatalog(false);
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
                this.loadCatalog(false);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            changeSort() {
                this.currentPage = 1;
                this.loadCatalog(false);
            },

            changeDirection(event) {
                if (event) event.preventDefault();
                this.currentSortDirection = (this.currentSortDirection === 'asc') ? 'desc' : 'asc';
                this.currentPage = 1;
                this.loadCatalog(false);
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
                let filterInput = { category_uid: { eq: String(this.categoryConfig.categoryUid) } };
                this.activeFilters.forEach(f => {
                    if (f.code === 'price') {
                        const [from, to] = f.value.split('_');
                        filterInput['price'] = {
                            from: String(from),
                            to: String(to)
                        };
                    } else if (f.code === 'category_uid') {
                        filterInput['category_uid'] = { eq: String(f.value) };
                    } else {
                        if (!filterInput[f.code]) {
                            filterInput[f.code] = { in: [] };
                        }
                        filterInput[f.code].in.push(String(f.value));
                    }
                });
                return filterInput;
            },

            loadCatalog(isLazy = false) {
                const self = this;
                this.isLoading = true;

                const aggregationsQuery = this.categoryConfig.isAnchor ? `
                    aggregations {
                        attribute_code
                        label
                        options {
                            label
                            value
                            count
                        }
                    }
                ` : '';

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
                                                ... on ConfigurableProduct {
                                                    swatches_html
                                                }
                                            }
                                            ${aggregationsQuery}
                                        }
                                    }`;

                return $.ajax({
                    url: '/graphql',
                    method: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'X-Async-Catalog-Category-Filter': 'intersect'
                    },
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

                            const newItems = data.items.map(item => {
                                item.url = item.url_rewrites?.[0] ? baseUrl + '/' + item.url_rewrites[0].url : '#';
                                return item;
                            });

                            if (isLazy) {
                                self.products = [...self.products, ...newItems];
                            } else {
                                self.products = newItems;
                            }

                            self.aggregations = data.aggregations || [];

                            self.stagedFilters = [...self.activeFilters];

                            self.broadcastFilters(self.activeFilters);
                        }
                    },
                    complete: function () {
                        self.isLoading = false;
                    }
                });
            },

            handleFilterClick(detail) {
                const { code, value, label, attrLabel } = detail;

                const exists = this.stagedFilters.find(f => f.code === code && f.value === value);

                if (exists) {
                    this.stagedFilters = this.stagedFilters.filter(f => f !== exists);
                } else {
                    this.stagedFilters.push({ code, value, label, attrLabel });
                }

                const isAutoUpdate = parseInt(this.moduleConfig.filtrationMode);

                if (isAutoUpdate) {
                    this.activeFilters = [...this.stagedFilters];
                    this.hasChanges = false;
                    this.currentPage = 1;
                    this.loadCatalog(false);
                } else {
                    this.hasChanges = JSON.stringify(this.stagedFilters) !== JSON.stringify(this.activeFilters);
                }

                this.broadcastFilters(this.stagedFilters);
            },

            broadcastFilters(filtersToDisplay) {
                const enrichedFilters = filtersToDisplay.map(f => {
                    if (f.label) return f;

                    const agg = this.aggregations.find(a => a.attribute_code === f.code);
                    const opt = agg?.options.find(o => String(o.value) === String(f.value));

                    return opt ? { ...f, label: opt.label, attrLabel: agg.label } : f;
                });

                const filteredAggs = this.aggregations.filter(agg =>
                    !enrichedFilters.some(f => f.code === agg.attribute_code) &&
                    !(agg.attribute_code === 'category_uid' && !this.categoryConfig.hasChildCategories)
                );

                window.dispatchEvent(new CustomEvent('catalog-filters-updated', {
                    detail: {
                        aggregations: filteredAggs,
                        activeFilters: enrichedFilters,
                        hasChanges: this.hasChanges
                    }
                }));
            },

            applyStagedFilters() {
                this.activeFilters = [...this.stagedFilters];
                this.hasChanges = false;
                this.currentPage = 1;
                this.loadCatalog(false);
                this.broadcastFilters(this.activeFilters);
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
                this.stagedFilters = [];

                const isAutoUpdate = parseInt(this.moduleConfig.filtrationMode);

                if (isAutoUpdate) {
                    this.activeFilters = [];
                    this.hasChanges = false;
                    this.currentPage = 1;

                    this.loadCatalog(false);
                } else {
                    this.hasChanges = this.activeFilters.length > 0;
                }

                this.broadcastFilters(this.stagedFilters);
            },

            lazyLoadNextPage() {
                if (this.isLoading) return;

                if (this.currentPage < this.getTotalPages()) {
                    this.currentPage++;
                    this.loadCatalog(true);
                }
            },

            updateUrl() {
                const params = new URLSearchParams();

                this.activeFilters.forEach(f => {
                    const current = params.get(f.code);
                    params.set(f.code, current ? `${current},${f.value}` : f.value);
                });

                const queryString = params.toString();
                const newUrl = window.location.pathname + (queryString ? '?' + queryString : '');

                if (window.location.search !== (queryString ? '?' + queryString : '')) {
                    window.history.pushState(null, '', newUrl);
                }

                return newUrl;
            },

            parseUrlToFilters() {
                const params = new URLSearchParams(window.location.search);
                const newFilters = [];

                params.forEach((value, code) => {
                    value.split(',').forEach(val => {
                        newFilters.push({
                            code: code,
                            value: val,
                            label: '',
                            attrLabel: ''
                        });
                    });
                });

                this.activeFilters = newFilters;
                this.stagedFilters = [...this.activeFilters];
            },
        }));

        if (!window.Alpine.initialized) {
            Alpine.start();
            window.Alpine.initialized = true;
        }
    };
});
