/*
 * Display special message if the reason for procuration is 'residency'
 */
export default () => {
    const dropdown = dom('#app_procuration_elections_reason');
    const message = dom('.procuration__reason__message');

    on(dropdown, 'change', () => {
        if ('residency' === dropdown.value) {
            removeClass(message, 'hide-me');
        } else {
            addClass(message, 'hide-me');
        }
    });
};
