import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Avasport brand — energetic athletic orange.
                brand: {
                    50: '#fff5ed',
                    100: '#ffe8d4',
                    200: '#fecda8',
                    300: '#fdab71',
                    400: '#fb7f38',
                    500: '#f85e12',
                    600: '#e94708',
                    700: '#c13409',
                    800: '#992a10',
                    900: '#7b2510',
                },
                // Deep navy "ink" for premium surfaces & admin chrome.
                ink: {
                    50: '#f3f6fb',
                    100: '#e3e9f3',
                    200: '#c7d3e7',
                    300: '#9fb2d3',
                    400: '#6f89ba',
                    500: '#4d68a1',
                    600: '#3b5186',
                    700: '#31426d',
                    800: '#2c395b',
                    900: '#0f1b32',
                    950: '#0a1222',
                },
            },
            boxShadow: {
                card: '0 1px 2px rgba(16,27,50,.06), 0 8px 24px -12px rgba(16,27,50,.18)',
            },
        },
    },
    plugins: [],
};
