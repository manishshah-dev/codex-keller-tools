import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { viteStaticCopy } from 'vite-plugin-static-copy'; // Import the plugin

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        // Add viteStaticCopy plugin configuration here
        viteStaticCopy({
            targets: [
                {
                    src: 'node_modules/tinymce/tinymce.min.js',
                    dest: 'vendor/tinymce'
                },
                {
                    src: 'node_modules/tinymce/skins',
                    dest: 'vendor/tinymce'
                },
                {
                    src: 'node_modules/tinymce/themes',
                    dest: 'vendor/tinymce'
                },
                {
                    src: 'node_modules/tinymce/icons',
                    dest: 'vendor/tinymce'
                },
                {
                    src: 'node_modules/tinymce/plugins',
                    dest: 'vendor/tinymce'
                }
            ]
        })
    ],
});
