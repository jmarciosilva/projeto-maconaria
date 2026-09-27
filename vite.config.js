import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        // Necessário para o container Docker aceitar conexões de fora (o
        // node service roda `vite --host 0.0.0.0`), mas as URLs dos assets
        // injetadas nas views devem sempre apontar para localhost, que é o
        // que o navegador do host acessa (dentro ou fora do Docker).
        origin: 'http://localhost:5173',
    },
});
