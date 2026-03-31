define(['async-catalog-core', 'jquery'], function (Core, $) {
    'use strict';

    Core.register('catalogData', (root, config) => ({
        // product data
        products: [],
        totalCount: 0,
        isLoading: false,

        // toolbar filters
        currentPage: 1,
        pageSize: parseInt(config.pageConfig.gridPerPageDefault) || 12,
        currentSortField: config.pageConfig.defaultSortBy || 'position',
        currentSortDirection: 'asc',

        // currency
        currencyCode: config.storeConfig.currencyCode || 'USD',

        //config
        pageConfig: config.pageConfig,
        moduleConfig: config.moduleConfig,




        init() {
            this.loadCatalog();
        },

        buildQuery() {
            const aggregationsQuery = config.categoryConfig.isAnchor
                ? `aggregations {
                       attribute_code
                       label
                       options { label value count }
                   }`
                : '';

            return `query GetCatalog(
                $filter: ProductAttributeFilterInput,
                $pageSize: Int,
                $currentPage: Int,
                $sort: ProductAttributeSortInput
            ) {
                products(
                    filter: $filter,
                    pageSize: $pageSize,
                    currentPage: $currentPage,
                    sort: $sort
                ) {
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
        },

        loadCatalog(isLazy) {
            this.isLoading = true;

            return $.ajax({
                url: '/graphql',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    query: this.buildQuery(),
                    variables: {
                        filter: root.catalogFiltersQuery.getPreparedFilters(),
                        pageSize: parseInt(this.pageSize),
                        currentPage: parseInt(this.currentPage),
                        sort: {
                            [this.currentSortField]: this.currentSortDirection.toUpperCase()
                        }
                    }
                }),
                success: (res) => {
                    if (res.data && res.data.products) {
                        const data = res.data.products;
                        const baseUrl = (window.BASE_URL || '').replace(/\/$/, '');

                        this.totalCount = data.total_count;
                        this.aggregations = data.aggregations || [];

                        const newItems = data.items.map(item => {
                            item.url = item.url_rewrites?.[0]
                                ? baseUrl + '/' + item.url_rewrites[0].url
                                : '#';
                            return item;
                        });

                        if (isLazy) {
                            this.products = [...this.products, ...newItems];
                        } else {
                            this.products = newItems;
                        }
                    }
                },
                error: (err) => {
                    console.error('[CatalogData] GraphQL Error:', err);
                },
                complete: () => {
                    this.isLoading = false;
                }
            });
        },

    }));
});
