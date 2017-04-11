/*
 * Procuration manager proposals list
 */
export default (queryString, totalCount, perPage, api) => {
    if (totalCount <= perPage) {
        return;
    }

    let page = 1;
    const button = dom('#btn-more');
    const loader = dom('#loader');
    const list = dom('#proposals-list');

    on(button, 'click', () => {
        hide(button);
        show(loader);

        page += 1;

        api.getProcurationProposals(queryString, page, (proposals) => {
            hide(loader);

            if (5 < proposals.length) {
                list.innerHTML += proposals;
                button.style.display = 'inline';
            }
        });
    });
};
