/*
 * Search Overlay
 */

export default () => {
    const searchOverlay = dom('#search-overlay');
    const siteBanner = dom('#header-banner');
    const siteHeader = dom('header');
    const siteContent = dom('main');
    const siteFooter = dom('footer');

    dom('#je-cherche').addEventListener('click', () => {
        addClass(searchOverlay, 'g-search');
        addClass(siteBanner, 'hide-me');
        addClass(siteHeader, 'hide-me');
        addClass(siteContent, 'hide-me');
        addClass(siteFooter, 'hide-me');
    });

    dom('#je-ferme-la-recherche').addEventListener('click', () => {
        removeClass(searchOverlay, 'g-search');
        removeClass(siteBanner, 'hide-me');
        removeClass(siteHeader, 'hide-me');
        removeClass(siteContent, 'hide-me');
        removeClass(siteFooter, 'hide-me');
    });
};
