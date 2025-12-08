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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#000000', // texto
                secondary: '#00CFFF', // botones
                background: '#F5F7FA', // fondo
                card: '#53a2d4', // fondo de tarjetas
                button: '#0B1D5F', // botones
            },
        },
    },

    plugins: [forms],
};
