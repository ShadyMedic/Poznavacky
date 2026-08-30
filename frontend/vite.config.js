import { defineConfig } from 'vite'
import preact from '@preact/preset-vite'

export default defineConfig({
    plugins: [preact()],

    server: {
        port: 5173,
        cors: true,
        origin: 'http://localhost:5173'
    },

    build: {
        outDir: '../js/dist',
        emptyOutDir: true,
        manifest: true
    }
})