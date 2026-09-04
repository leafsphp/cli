import { defineConfig } from 'vite';
import leaf from '@leafphp/vite-plugin';

export default defineConfig({
    plugins: [
        leaf({
            input: ['views/js/app.js', 'views/css/app.css'],
            refresh: true,
        }),
    ],
});
