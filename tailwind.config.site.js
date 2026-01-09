const defaultTheme = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');

/** @type {import('tailwindcss').Config} */
module.exports = {
    safelist: [
        'form-group',
        'form-control',
        'border-ui_gray-1',
        'text-ui_blue-60',
        'hover:bg-white',
        'hover:border-ui_gray-20',
        'border-ui_blue-50',
        'opacity-60',
        'cursor-not-allowed',
    ],
    theme: {
        extend: {
            colors: {
                ui_blue: {
                    1: '#EBF3FF',
                    5: '#EEF5FF',
                    20: '#CBE1FF',
                    30: '#8AC3FF',
                    40: '#4291E1',
                    50: '#0084FF',
                    60: '#0077E5',
                    70: '#006ACC',
                },
                ui_gray: {
                    1: '#F9FAFB',
                    5: '#F9F9F9',
                    10: '#F2F5F7',
                    20: '#E1E5E8',
                    30: '#D1D5DA',
                    40: '#B5BDC1',
                    50: '#919EAB',
                    60: '#737F87',
                    80: '#3F4951',
                    90: '#212B36',
                },
                ui_red: {
                    100: '#AB0C0C',
                    70: '#D02828',
                    50: '#FF3333',
                    20: '#FFDCDC',
                    5: '#FFF8F8',
                },
                ui_yellow: {
                    5: '#FFFCF0',
                    20: '#FFF5CF',
                    30: '#F2E4AD',
                    45: '#FFDA46',
                    50: '#FFD633',
                    70: '#F4C300',
                    80: '#D79C31',
                    90: '#916517',
                    100: '#5B4803',
                },
                ui_green: {
                    5: '#F8FFFA',
                    20: '#DCF5E3',
                    30: '#9FDDB0',
                    50: '#3FBA61',
                    60: '#39A757',
                    70: '#32954E',
                    90: '#287E3F',
                    100: '#176F2F',
                },
                re: {
                    blue: {
                        50: '#F3F6FD',
                        100: '#D2DFFB',
                        200: '#A2BEF7',
                        300: '#5D8EF1',
                        400: '#2E6EEE',
                        500: '#1254D8',
                        600: '#104CC2',
                        700: '#0E3FA2',
                        800: '#0B3282',
                        900: '#09296A',
                    },
                    green: {
                        50: '#EAF6F4',
                        100: '#D7F4EE',
                        200: '#C2EEE5',
                        300: '#ACE8DC',
                        400: '#60CBB6',
                        500: '#2EA78F',
                        600: '#299681',
                        700: '#237D6B',
                        800: '#1C6456',
                        900: '#175246',
                    },
                },
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
                },
            },
            fontFamily: {
                maax: ['Maax', ...defaultTheme.fontFamily.sans],
                din: ['Din', 'sans-serif'],
                machinepro: ['MachinePro', 'sans-serif'],
                value: ['Value', 'sans-serif'],
                sharp: ['Sharp Grotesk', 'sans-serif'],
                space: ['Space Grotesk', 'sans-serif'],
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
            maxWidth: {
                '8xl': '90rem',
            },
        },
    },
    plugins: [require('@tailwindcss/forms')({ strategy: 'base' }), require('@tailwindcss/aspect-ratio'), require('@tailwindcss/typography')],
};
