define(['async-catalog-core'], function (Core) {
    'use strict';

    Core.register('catalogToolbarAmount', (root, config) => ({
        amountText: '',

        init() {
            root.$watch('catalogData.totalCount', () => this.getAmountText());
            root.$watch('catalogData.pageSize', () => this.getAmountText());
            root.$watch('catalogData.currentPage', () => this.getAmountText());

        },

        getAmountText() {
            const total = parseInt(root.catalogData.totalCount);
            const size = parseInt(root.catalogData.pageSize);
            const current = parseInt(root.catalogData.currentPage);


            if (total === 0) {
                this.amountText = '';
                return;
            }

            const first = ((current - 1) * size) + 1;
            const last = Math.min(current * size, total);

            if (total > size) {
                this.amountText = `Items ${first}-${last} of ${total}`;
                return;
            }

            this.amountText = total === 1 ? `1 Item` : `${total} Items`;
        },
    }));
});
