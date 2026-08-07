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
            colors: {
                'brand-red': '#C8202D',
                'brand-red-dark': '#9c1822',
                'brand-white': '#FFFFFF',
                'brand-offwhite': '#F7F5F4',
                'brand-charcoal': '#2A2A2C',
                'brand-grey': '#7A7A7E',
            },
            fontFamily: {
                display: ['"Archivo Black"', ...defaultTheme.fontFamily.sans],
                sans: ['"Sora"', ...defaultTheme.fontFamily.sans],
                mono: ['"IBM Plex Mono"', ...defaultTheme.fontFamily.mono],
            },
        },
    },

    plugins: [forms],
};
