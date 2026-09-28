import template from './conventions-product-detail-labels.html.twig';
import './conventions-product-detail-labels.scss';

export default {
    template,

    inject: [
        'acl',
    ],

    computed: {
        product() {
            return Shopware.Store.get('swProductDetail').product;
        },

        isLoading() {
            return Shopware.Store.get('swProductDetail').isLoading;
        },

        // Loaded by the productCriteria override in extension/sw-product-detail
        // and saved together with the product by the normal "Save" button
        labels() {
            return this.product?.extensions?.labels ?? null;
        },
    },
};
