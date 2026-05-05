import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Tajawal', ...defaultTheme.fontFamily.sans],
                tajawal: ['Tajawal', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#eef7ff',
                    100: '#d9ecff',
                    200: '#b8dbff',
                    300: '#84c0ff',
                    400: '#4ca0ff',
                    500: '#1d7ff4',
                    600: '#0d66d8',
                    700: '#0e52af',
                    800: '#114589',
                    900: '#12396f',
                    950: '#081c3a',
                },
            },
        },
    },

    plugins: [forms],
};
