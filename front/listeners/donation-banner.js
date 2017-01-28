/*
 * Handle the click on the donation banner close button by saving the information in a cookie.
 */
export default (di) => {
    let cookies = di.get('cookies');

    if (typeof cookies.get('banner_donation') === 'undefined') {
        let banner = dom('#header-banner'),
            bannerButton = dom('#header-banner-close-btn');

        show(banner);

        on(bannerButton, 'click', () => {
            hide(banner);
            cookies.set('banner_donation', 'dismiss', { expires: 1 });
        });
    }
};
