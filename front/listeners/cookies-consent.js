/*
 * Initialize the cookies consent library.
 */
export default () => {
    cookieconsent.initialise({
        palette: {
            popup: { background: '#edf861' },
            button: { background: '#ffffff' },
        },
        position: 'bottom',
        content: {
            message: 'Ce site utilise des cookies — ',
            dismiss: 'OK',
            link: 'En savoir plus',
            href: '/mentions-legales',
        },
        layout: 'en-marche',
        layouts: {
            'en-marche': '<div class="cc-window__text">{{message}}{{link}}</div>' +
            '<div class="cc-window__button">{{compliance}}</div>',
        },
    });
};
