/*
 * Social share
 */
export default () => {
    const confirmMessage = dom('#confirm-message');

    findAll(dom('#je-partage'), '.social__copy').forEach((item) => {
        on(item, 'click', () => {
            addClass(confirmMessage, 'je-partage__copy--flash');
            setTimeout(() => { removeClass(confirmMessage, 'je-partage__copy--flash'); }, 700);
        });
    });
};
