import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    amber: {
                        DEFAULT: '#E06810',
                        light: '#FDE6D4',
                        dark: '#A84D08',
                        50: '#FFF3EB',
                        100: '#FDE6D4',
                        200: '#FAC9A8',
                        300: '#F5A872',
                        400: '#EF8B48',
                        500: '#E06810',
                        600: '#C05A0E',
                        700: '#A04B0C',
                        800: '#803C09',
                        900: '#602D07',
                    },
                    navy: {
                        DEFAULT: '#1E3A5F',
                        light: '#E8EDF3',
                        50: '#F0F3F8',
                        100: '#E8EDF3',
                        200: '#C5D1E3',
                        300: '#9DB3CC',
                        400: '#6E8FB0',
                        500: '#1E3A5F',
                        600: '#182E4C',
                        700: '#122339',
                        800: '#0C1726',
                        900: '#060C13',
                    },
                    warm: {
                        DEFAULT: '#FFFAF5',
                        dark: '#F5EDE4',
                    },
                },
            },
            borderRadius: {
                'brand': '12px',
            },
        },
    },
    plugins: [],
};
