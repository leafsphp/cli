import { defineConfig } from 'vite';
import leaf from '@leafphp/vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        leaf({
            input: ['app/views/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
});
