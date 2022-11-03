const defaultTheme = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        'templates/**/*.html.twig',
        'assets/**/*.js',
    ],
    safelist: [
        'form-group',
        'form-control',
    ],
    theme: {
        extend: {
            colors: {
                'lighter-blue': '#EDF5FF',
                'light-blue': {
                    DEFAULT: '#73C0F1',
                    500: '#1254D8',
                },
                'dark-blue': '#00205F',
                gray: {
                    DEFAULT: colors.gray[50],
                    border: colors.gray[200],
                    ...colors.gray,
                },
                'dark-gray': colors.slate[900],
                'medium-gray': colors.slate[600],
                'background-gray': colors.gray[50],
                green: {
                    DEFAULT: '#2EA78F',
                    ...colors.green,
                },
                'lighter-green': '#2EA78F14',
                'light-green': colors.emerald[200],
                red: {
                    DEFAULT: '#F00032',
                    ...colors.red,
                },
                'light-red': '#FFC9D4',
                yellow: {
                    DEFAULT: colors.yellow[500],
                    ...colors.yellow,
                }
            },
            fontFamily: {
                maax: ['Maax', ...defaultTheme.fontFamily.sans],
                din: ['Din', 'sans-serif'],
            },
            fontSize: {
                '4xl': ['2.5rem', { lineHeight: '3.125rem' }],
            },
            spacing: {
                15: '3.75rem',
                25: '6.25rem',
                27: '6.875rem',
                38: '9.575rem',
                182: '45.625rem',
            },
            typography: (theme) => ({
                DEFAULT: {
                    css: {
                        color: theme('colors.gray.600'),
                        a: {
                            color: theme('colors.light-blue-500'),
                            '&:hover': {
                                color: theme('colors.dark-blue'),
                            },
                        },
                    },
                },
            }),
        },
    },
    plugins: [
        require('@tailwindcss/forms')({ strategy: 'base' }),
        require('@tailwindcss/typography'),
    ],
};
