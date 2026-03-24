var config = {
    map: {
        '*': {
            'alpine': 'Perspective_AsyncCatalog/js/lib/alpine',
            'alpine-intersect': 'Perspective_AsyncCatalog/js/lib/alpine-intersect'
        }
    },
    shim: {
        'alpine': {
            'exports': 'Alpine'
        },

        'alpine-intersect': {
            'deps': ['alpine'],
            'exports': 'Alpine'
        }
    }
};
