import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                // Usadas apenas no site público (ver components/layouts/site.blade.php),
                // para não afetar a tipografia do painel administrativo/autenticação.
                siteSans: ['"Source Sans 3"', ...defaultTheme.fontFamily.sans],
                siteDisplay: ['"Source Serif 4"', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // Paleta extraída do selo real da Loja (public/images/logo-loja.png):
                // azul-marinho do texto/símbolo e azul-claro do anel, sobre branco
                // predominante — ver docs/MODULOS.md (seção "CMS e landing page").
                brand: {
                    navy: '#1d2a5c',
                    navyDeep: '#11193b',
                    sky: '#dcecf2',
                    skyDeep: '#a9cfe0',
                    ink: '#1b2233',
                    inkSoft: '#4a5268',
                    paperSoft: '#e9f3f8',
                },
            },
        },
    },

    plugins: [forms, typography],
};
