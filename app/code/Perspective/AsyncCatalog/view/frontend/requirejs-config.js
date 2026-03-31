var config = {
    map: {
        '*': {
            'alpine': 'Perspective_AsyncCatalog/js/lib/alpine',
            'alpine-intersect': 'Perspective_AsyncCatalog/js/lib/alpine-intersect',
            'async-catalog-core': 'Perspective_AsyncCatalog/js/async-catalog-core',
            'catalog-view-mode': 'Perspective_AsyncCatalog/js/catalog/toolbar/view-mode',
            'catalog-data-graphql': 'Perspective_AsyncCatalog/js/catalog/data/catalog-data-graphql',
            'catalog-item-price': 'Perspective_AsyncCatalog/js/catalog/item/price',
            'catalog-add-to-cart': 'Perspective_AsyncCatalog/js/catalog/item/actions/add-to-cart',
            'catalog-additional-actions': 'Perspective_AsyncCatalog/js/catalog/item/actions/additional-actions',
            'catalog-toolbar-amount': 'Perspective_AsyncCatalog/js/catalog/toolbar/amount',
            'catalog-toolbar-sorter': 'Perspective_AsyncCatalog/js/catalog/toolbar/sorter',
            'catalog-toolbar-limiter': 'Perspective_AsyncCatalog/js/catalog/toolbar/limiter',
            'catalog-toolbar-pager': 'Perspective_AsyncCatalog/js/catalog/toolbar/pager',
            'catalog-lazyload': 'Perspective_AsyncCatalog/js/catalog/additional/lazyload',
            'catalog-loader': 'Perspective_AsyncCatalog/js/catalog/additional/loader',
            'catalog-filters-data': 'Perspective_AsyncCatalog/js/catalog/filters/data',
            'catalog-filters-url': 'Perspective_AsyncCatalog/js/catalog/filters/url',
            'catalog-filters-query': 'Perspective_AsyncCatalog/js/catalog/filters/query',
            'catalog-filters-actions': 'Perspective_AsyncCatalog/js/catalog/filters/actions',
            'catalog-filters-ui': 'Perspective_AsyncCatalog/js/catalog/filters/ui',
            'catalog-item-swatch': 'Perspective_AsyncCatalog/js/catalog/item/swatch',
            'catalog-filters-swatch': 'Perspective_AsyncCatalog/js/catalog/filters/swatch',
        }
    },
    config: {
        'Perspective_AsyncCatalog/js/async-catalog-core': {
            plugins: [
                'alpine-intersect'
            ],
            extensions: [
                'Perspective_AsyncCatalog/js/catalog/toolbar/view-mode',
                'Perspective_AsyncCatalog/js/catalog/data/catalog-data-graphql',
                'Perspective_AsyncCatalog/js/catalog/item/price',
                'Perspective_AsyncCatalog/js/catalog/item/actions/add-to-cart',
                'Perspective_AsyncCatalog/js/catalog/item/actions/additional-actions',
                'Perspective_AsyncCatalog/js/catalog/toolbar/amount',
                'Perspective_AsyncCatalog/js/catalog/toolbar/sorter',
                'Perspective_AsyncCatalog/js/catalog/toolbar/limiter',
                'Perspective_AsyncCatalog/js/catalog/toolbar/pager',
                'catalog-lazyload',
                'catalog-loader',
                'catalog-filters-data',
                'catalog-filters-url',
                'catalog-filters-query',
                'catalog-filters-actions',
                'catalog-filters-ui',
                'catalog-item-swatch',
                'catalog-filters-swatch'
            ]
        }
    }
};
