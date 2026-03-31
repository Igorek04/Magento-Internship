define(['async-catalog-core', 'Magento_Customer/js/customer-data', 'jquery'], function (Core, CustomerData, $) {
    'use strict';

    Core.register('catalogAdditionalActions', (root, config) => ({

        init() {

        },

        addToWishlist(productId) {
            const url = window.BASE_URL + 'wishlist/index/add/product/' + productId + '/';
            this.postAction(url, {product: productId});
        },

        addToCompare(productId) {
            const url = window.BASE_URL + 'catalog/product_compare/add/product/' + productId + '/';
            this.postAction(url, {product: productId});
        },

        postAction(url, data) {
            $.ajax({
                url: url,
                data: Object.assign(data, {
                    'uenc': root.catalogAddToCart.getUenc(),
                    'form_key': root.catalogAddToCart.getFormKey()
                }),
                type: 'post',
                showLoader: true,
                success: (res) => {
                    if (typeof res === 'string') {
                        if (res.includes('customer-account-login') ||
                            res.includes('class="login"') ||
                            res.includes('id="customer-login-form"')) {
                            window.location.href = window.BASE_URL + 'customer/account/login/';
                            return;
                        }
                    }

                    const sections = ['wishlist', 'compare-products', 'messages'];
                    CustomerData.invalidate(sections);
                    CustomerData.reload(sections, true);
                }
            });
        },

    }));
});
