/** @type {import('tailwindcss').Config} */
module.exports = {
    presets: [
        require('./tailwind.config.site'),
    ],
    content: [
        'templates/**/*.html.twig',
        'assets/**/*.js',
        './src/Twig/Components/**/*.php',
    ],
    theme: {
        container: {
            center: true,
        },
        screens: {
            xs: '375px',
            sm: '640px',
            md: '768px',
            lg: '1024px',
            xl: '1280px',
        },
    },
    plugins: [],
};
