/*
 * Legislatives candidates list
 */
export default () => {
    const department = dom('#department');
    const career = dom('#career');
    const search = dom('#search');
    const results = findAll(document, '.legislatives__trombi__item');
    const noResults = dom('.legislatives__no_results');

    let filteredResults = findAll(document, '.legislatives__trombi__item:not(.hidden)');

    function filterResults(value, attribute) {
        filteredResults.forEach((element) => {
            const attributeValue = element.getAttribute(attribute);

            if ('' === value || attributeValue === value) {
                removeClass(element, 'hidden');
            } else {
                addClass(element, 'hidden');
            }
        });

        filteredResults = findAll(document, '.legislatives__trombi__item:not(.hidden)');
    }

    function searchOnFilteredResults() {
        filteredResults.forEach((element) => {
            const name = element.getAttribute('data-name');

            if (-1 !== name.search(new RegExp(search.value, 'i'))) {
                removeClass(element, 'hidden');
            } else {
                addClass(element, 'hidden');
            }
        });
    }

    function toggleNoResults(length) {
        if (0 === length) {
            removeClass(noResults, 'hidden');
        } else {
            addClass(noResults, 'hidden');
        }
    }

    function filterCallback() {
        filteredResults = results;
        filterResults(career.value, 'data-career');
        filterResults(department.value, 'data-zone');
        searchOnFilteredResults();
        toggleNoResults(findAll(document, '.legislatives__trombi__item:not(.hidden)').length);
    }

    on(department, 'change', filterCallback);
    on(career, 'change', filterCallback);
    on(search, 'input', searchOnFilteredResults);
};
