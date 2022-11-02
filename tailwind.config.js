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
    },
    plugins: [
        require('@tailwindcss/line-clamp'),
    ],
};
