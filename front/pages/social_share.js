/*
 * Social share
 */
import Clipboard from 'clipboard';

export default (urlAll, urlCategory) => {
    if (Clipboard.isSupported()) {
        const confirmMessage = dom('#confirm-message');

        new Clipboard('.social__copy');

        findAll(dom('#je-partage'), '.social__copy').forEach((item) => {
            show(item);
            on(item, 'click', () => {
                addClass(confirmMessage, 'je-partage__copy--flash');
                setTimeout(() => {
                    removeClass(confirmMessage, 'je-partage__copy--flash');
                }, 700);
            });
        });
    }

    const categoryChooser = dom('#je-partage-category');

    on(categoryChooser, 'change', () => {
        const category = categoryChooser.options[categoryChooser.selectedIndex].value;
        window.location.href = 'all' === category ? urlAll : urlCategory.replace('slug', category);
    });
};
