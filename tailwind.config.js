module.exports = {
    content: [
        'templates/**/*.html.twig',
        'assets/**/*.js',
    ],
    theme: {
        container: {
            center: true,
        },
        screens: {
            'md': '768px',
            'lg': '1024px',
            'xl': '1280px',
        },
        extend: {
            fontFamily: {
                maax: 'Maax',
                din: 'Din',
            },
            colors: {
                'light-blue': '#0370ED',
                'dark-blue': '#000ABB',
                green: '#00CF94',
                gray: '#EEEEEE',
                'medium-gray': '#555555',
                'background-gray': '#F9F9F9;',
                'dark-gray': '#222222',
                'gray-border': '#DDDDDD',
                yellow: '#FFDC6B',
            },
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
            fontSize: {
                // font-size: 11px, line-height: 16px
                'custom-xs': ['0.6875rem', '1rem'],
                // font-size: 11px, line-height: 16px
                'custom-sm': ['0.8125rem', '1.25rem'],
                // font-size: 15px, line-height: 24px
                'custom-base1': ['0.9375rem', '1.5rem'],
                // font-size: 16px, line-height: 25px
                'custom-base2': ['1rem', '1.5625rem'],
                // font-size: 18px, line-height: 25px
                'custom-lg': ['1.125rem', '1.5625rem'],
                // font-size: 28px, line-height: 32px
                custom: ['1.75rem', '2rem'],
                // font-size: 40px, line-height: 50px
                'custom-xl': ['2.5rem', '3.125rem'],
                // font-size: 25px, line-height: 32px
                'custom-2xl': ['1.5625rem', '2rem'],
            },
        },
    },
    safelist: [
        'form-group',
        'form-control',
    ],
    plugins: [
        require('@tailwindcss/forms')({ strategy: 'base' }),
    ],
};
