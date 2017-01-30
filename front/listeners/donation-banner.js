/*
 * Handle the click on the donation banner close button by saving the information in a cookie.
 */
export default (di) => {
    const banner = dom('#header-banner');
    const bannerButton = dom('#header-banner-close-btn');

    if (!banner) {
        return;
    }

    const cookies = di.get('cookies');

    if ('undefined' === typeof cookies.get('banner_donation')) {
        show(banner);

        on(bannerButton, 'click', () => {
            hide(banner);
            cookies.set('banner_donation', 'dismiss', { expires: 1 });
        });
    }
};
