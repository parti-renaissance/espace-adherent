export default function (countryFieldSelector, stateFieldSelector) {
    const countryElement = dom(countryFieldSelector);
    const stateElement = dom(stateFieldSelector);

    changeStateFieldVisibility(countryElement, stateElement);

    on(countryElement, 'change', () => changeStateFieldVisibility(countryElement, stateElement));
}

function changeStateFieldVisibility(country, state) {
    if ('FR' === country.value) {
        state.classList.add('hidden');
    } else {
        state.classList.remove('hidden');
    }
}
