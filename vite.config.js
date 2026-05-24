import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        rollupOptions: {
            input: 'resources/css/orbit.css',
            output: {
                assetFileNames: 'css/orbit[extname]',
            },
        },
        outDir: 'dist',
        emptyOutDir: false,
        manifest: false,
    },
});
