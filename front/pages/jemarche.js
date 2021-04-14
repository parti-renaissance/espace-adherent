/*
 * Je Marche
 */
export default () => {
    window.openAction = (element, actionName) => {
        findAll(document, '.action__item').forEach((item) => {
            hide(item);
        });

        findAll(document, '.action__menu__item').forEach((tabLink) => {
            removeClass(tabLink, 'action__menu__item--active');
        });

        dom(`#${actionName}`).style.display = 'flex';
        addClass(element, 'action__menu__item--active');
    };
};
