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
                'light-blue': '#73C0F1',
                'light-blue-500': '#1254D8',
                'dark-blue': '#00205F',
                gray: {
                    DEFAULT: colors.gray[300],
                    border: colors.gray[300],
                    ...colors.gray,
                },
                'dark-gray': colors.slate[900],
                'medium-gray': colors.slate[500],
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
        }
    },
    plugins: [
        require('@tailwindcss/forms')({ strategy: 'base' }),
        require('@tailwindcss/typography'),
    ],
};
