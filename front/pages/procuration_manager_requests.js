/*
 * Procuration manager requests list
 */
export default (totalCount, perPage, api) => {
    if (totalCount <= perPage) {
        return;
    }

    let page = 1;
    const button = dom('#btn-more');
    const loader = dom('#loader');
    const list = dom('#requests-list');

    on(button, 'click', () => {
        hide(button);
        show(loader);

        page += 1;

        api.getProcurationRequests(page, (requests) => {
            hide(loader);

            if (5 < requests.length) {
                list.innerHTML += requests;
                button.style.display = 'inline';
            }
        });
    });
};
