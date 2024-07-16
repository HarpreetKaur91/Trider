import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    server: { 
        hmr: {
            host: 'localhost',
        },
    }, 
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/css/style.css',
                'resources/css/vendors/css/vendor.bundle.base.css',          
                'resources/js/jquery.cookie.js',
                'resources/js/off-canvas.js',
                'resources/js/hoverable-collapse.js',
                'resources/js/misc.js',
                'resources/js/file-upload.js'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '$': 'jQuery',
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
            '~font' : path.resolve(__dirname,'resources/fonts/Ubuntu')
        }
    },
});
