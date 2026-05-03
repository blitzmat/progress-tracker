import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    optimizeDeps: {
        exclude: ['@coderline/alphatab'],   // ← exclude alphaTab from pre-bundling
    },
    // Add this server block for DDEV compatibility
    server: {
        host: '0.0.0.0', // Allows connections from outside the container
        port: 5173,
        strictPort: true,
        // Use your actual DDEV project name. Check with `ddev describe`
        origin: 'https://progress-tracker.ddev.site:5175',
        cors: {
            // This is the key. Allow requests from your main DDEV origin.
            origin: 'https://progress-tracker.ddev.site',
            // You can also use a function for more control, but this is simpler.
            // origin: (origin) => origin?.startsWith('https://progress-tracker.ddev.site') ? origin : false,
        },
        // Also add headers to ensure the correct CORS headers are sent
        headers: {
            // This is the crucial line. It tells the browser to allow requests from your main domain.
            'Access-Control-Allow-Origin': 'https://progress-tracker.ddev.site',
            // You might need to add these for preflight requests, but the line above is often enough.
            'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
            'Access-Control-Allow-Headers': 'X-Requested-With, content-type, Authorization',
        },
    },
});

