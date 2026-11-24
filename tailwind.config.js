import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

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
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                navy: {
                    50: '#f0f4f8',
                    100: '#d9e2ec',
                    200: '#bcccdc',
                    300: '#9fb3c8',
                    400: '#627d98',
                    500: '#486581',
                    600: '#334e68',
                    700: '#1e3a8a',
                    800: '#172554',
                    900: '#0f172a',
                    950: '#0a0f1d',
                },
                beige: {
                    50: '#fdfbf7',
                    100: '#f7f2e9',
                    200: '#eee3d0',
                    300: '#e2ceb1',
                    400: '#cfb28d',
                    500: '#bd976c',
                    600: '#aa7e53',
                    700: '#8c6342',
                    800: '#725139',
                    900: '#5f4432',
                },
            },
        },
    },

    plugins: [forms],
};
