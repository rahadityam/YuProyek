// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path'; // Tambahkan ini jika ingin menggunakan alias

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', // Path ke file CSS utama Anda
                'resources/js/app.js',   // Path ke file JS utama Anda
            ],
            refresh: true, // Mengaktifkan hot-reload untuk file Blade dan route
        }),
    ],
    // (Opsional) Tambahkan alias jika Anda sering mengimpor dari path tertentu
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            // Anda bisa menambahkan alias lain di sini
        },
    },
});