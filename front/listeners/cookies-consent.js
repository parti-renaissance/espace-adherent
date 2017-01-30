/*
 * Initialize the cookies consent library.
 */
export default () => {
    cookieconsent.initialise({
        palette: {
            popup: { background: '#e5e5e5' },
            button: { background: '#55f0b8' },
        },
        position: 'bottom-right',
        content: {
            message: 'Ce site utilise des cookies\n',
            dismiss: 'OK',
            link: 'En savoir plus',
            href: '/mentions-legales',
        },
    });
};
