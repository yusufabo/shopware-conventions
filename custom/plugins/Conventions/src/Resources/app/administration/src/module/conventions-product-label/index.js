import './extension/sw-product-detail';

Shopware.Component.register('conventions-product-label-list', () => import('./page/conventions-product-label-list'));
Shopware.Component.register('conventions-product-label-detail', () => import('./page/conventions-product-label-detail'));
Shopware.Component.extend(
    'conventions-product-label-create',
    'conventions-product-label-detail',
    () => import('./page/conventions-product-label-create'),
);
Shopware.Component.register('conventions-product-detail-labels', () => import('./view/conventions-product-detail-labels'));

Shopware.Module.register('conventions-product-label', {
    type: 'plugin',
    name: 'conventions-product-label',
    title: 'conventions-product-label.general.mainMenuItemGeneral',
    description: 'conventions-product-label.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'regular-products',
    entity: 'product_label',

    routes: {
        list: {
            component: 'conventions-product-label-list',
            path: 'list',
        },
        create: {
            component: 'conventions-product-label-create',
            path: 'create',
            meta: {
                parentPath: 'conventions.product.label.list',
            },
        },
        detail: {
            component: 'conventions-product-label-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'conventions.product.label.list',
            },
        },
    },

    // Adds the "Labels" tab to the product detail page
    routeMiddleware(next, currentRoute) {
        const routeName = 'sw.product.detail.labels';

        if (
            currentRoute.name === 'sw.product.detail' &&
            currentRoute.children.every((child) => child.name !== routeName)
        ) {
            currentRoute.children.push({
                name: routeName,
                path: '/sw/product/detail/:id/labels',
                component: 'conventions-product-detail-labels',
                meta: {
                    parentPath: 'sw.product.index',
                    privilege: 'product.viewer',
                },
            });
        }

        next(currentRoute);
    },

    navigation: [
        {
            id: 'conventions-product-label',
            path: 'conventions.product.label.list',
            label: 'conventions-product-label.general.mainMenuItemGeneral',
            parent: 'sw-catalogue',
            privilege: 'product.viewer',
            position: 100,
        },
    ],
});
