/** @type {import('tailwindcss').Config} */
module.exports = {
    presets: [
        require('./tailwind.config.site'),
    ],
    content: [
        'templates/**/*.html.twig',
        'assets/**/*.js',
    ],
    theme: {
        container: {
            center: true,
        },
        screens: {
            md: '768px',
            lg: '1024px',
            xl: '1280px',
        },
        extend: {
            spacing: {
                3.25: '0.8125rem',
                3.75: '0.9375rem',
                4.25: '1.0625rem',
                4.75: '1.1875rem',
                5.5: '1.375rem',
                5.75: '1.4375rem',
                6.25: '1.5625rem',
                7.5: '1.875rem',
                7.75: '1.9375rem',
                8.75: '2.1875rem',
                10.25: '2.5625rem',
                11.25: '2.8125rem',
                12.5: '3.125rem',
                15: '3.75rem',
                16.25: '4.0625rem',
                25: '6.25rem',
                30: '7.5rem',
                31.25: '7.8125rem',
                37.5: '9.375rem',
                50: '12.5rem',
                105: '26.25rem',
            },
            borderWidth: {
                0.5: '0.5px',
                1: '1px',
            },
            lineHeight: {
                4.5: '1.125rem',
                6.25: '1.5625rem',
                7.5: '1.875rem',
            },
        },
    },
    plugins: [
        require('@tailwindcss/line-clamp'),
    ],
};
