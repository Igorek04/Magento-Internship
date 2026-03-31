define(['async-catalog-core'], function (Core) {
    'use strict';

    Core.register('catalogToolbarPager', (root, config) => ({

        init() {

        },

        getTotalPages() {
            return Math.ceil(root.catalogData.totalCount / root.catalogData.pageSize) || 0;
        },

        getFramePages() {
            const total = this.getTotalPages();
            if (total <= 1) return [];

            const current = parseInt(root.catalogData.currentPage);
            const delta = 2;
            let pages = [];

            for (let i = 1; i <= total; i++) {
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
        },

        changePage(pageNumber) {
            root.catalogData.currentPage = parseInt(pageNumber);
            root.catalogData.loadCatalog(false);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

    }));
});
