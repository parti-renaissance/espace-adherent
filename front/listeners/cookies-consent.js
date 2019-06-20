/*
 * Initialize the cookies consent library.
 */
export default () => {
    cookieconsent.initialise({
        palette: {
            popup: { background: 'rgba(255,255,255,0.95)' },
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
        elements: {
            message: '<span id="cookieconsent:desc" class="text--dark">{{message}}</span>',
            dismiss: '<a aria-label="dismiss cookie message" tabindex="0" role="button" class="btn cc-btn cc-dismiss">{{dismiss}}</a>',
            link: '<a aria-label="learn more about cookies" tabindex="0" '
             + 'class="text--blue--dark link--no-decor" href="{{href}}" target="_blank">{{link}}</a>',
        },
    });
};
