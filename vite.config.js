import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-produk.js'],
            refresh: true,
        }),
    ],
    // server: {
    //     host: '0.0.0.0', // Membuka akses ke jaringan
    //     hmr: {
    //         host: '192.168.1.20', // <--- GANTI DENGAN IP ASLI LAPTOP ANDA
    //     },
    // },
    
});
