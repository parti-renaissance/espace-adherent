function changeFieldsVisibility(country, state) {
    if ('FR' === country.value) {
        state.classList.add('hidden');
        state.value = '';
    } else {
        state.classList.remove('hidden');
    }
}

export default (countryElement, stateFieldSelector) => {
    const stateElement = dom(stateFieldSelector);

    changeFieldsVisibility(countryElement, stateElement);

    on(countryElement, 'change', () => changeFieldsVisibility(countryElement, stateElement));
};
