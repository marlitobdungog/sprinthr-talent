import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import {
    defineConfig
} from 'vite';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.jsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        origin: process.env.VITE_DEV_SERVER_ORIGIN,
        cors: process.env.VITE_CORS_ORIGIN
            ? { origin: process.env.VITE_CORS_ORIGIN }
            : undefined,
        strictPort: true,
        hmr: {
            host: 'localhost',
            clientPort: Number(process.env.VITE_HMR_CLIENT_PORT ?? 5173),
        },
        watch: {
            usePolling: true,
            interval: 500,
        },
    },
});
