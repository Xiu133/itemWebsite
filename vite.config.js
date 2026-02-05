import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/ecommerce/style.css',
                'resources/js/ecommerce/app.js',
                'resources/css/commodity/style.css',
                'resources/js/commodity/app.js',
                'resources/css/brandstory/style.css',
                'resources/css/aboutus/style.css',
                'resources/css/search/style.css',
                'resources/js/search/app.js',
                'resources/css/discount/style.css',
                'resources/js/discount/app.js',
                'resources/css/orders/style.css',
                'resources/css/checkout/style.css',
                'resources/js/checkout/app.js',
                'resources/css/merchant/dashboard.css',
                'resources/js/merchant/dashboard.js',
                'resources/css/product/style.css',
                'resources/css/product/create.css',
                'resources/css/product/edit.css',
                'resources/css/product/delete.css',
                'resources/js/product/app.js',
                'resources/js/product/create.js',
                'resources/js/product/edit.js',
                'resources/js/product/delete.js',
                'resources/css/inventory/style.css',
                'resources/js/inventory/app.js',
                'resources/css/orders/manage.css',
                'resources/js/orders/manage.js',
                'resources/css/payments/manage.css',
                'resources/js/payments/manage.js',
                'resources/js/logistics/manage.js',
                'resources/css/delivery/style.css'
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
