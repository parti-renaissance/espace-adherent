export default function (
    countryFieldSelector,
    postalCodeFieldSelector,
    stateFieldSelector
) {
    const countryElement = dom(countryFieldSelector);
    const postalCodeElement = dom(postalCodeFieldSelector);
    const stateElement = dom(stateFieldSelector);

    changeFieldsVisibility(countryElement, postalCodeElement, stateElement);

    on(countryElement, 'change', () => changeFieldsVisibility(countryElement, postalCodeElement, stateElement));
}

function changeFieldsVisibility(country, postalCode, state) {
    if ('FR' === country.value) {
        state.classList.add('hidden');
        postalCode.classList.remove('hidden');
    } else {
        state.classList.remove('hidden');
        postalCode.classList.add('hidden');
    }
}
