import AutocompletedAddressForm from '../services/address/AutocompletedAddressForm';
import AddressObject from '../services/address/AddressObject';
import changeFieldsVisibility from '../services/form/changeFieldsVisibility';

export default (countryFieldSelector, postalCodeFieldSelector, stateFieldSelector) => {
    const countryElement = dom(countryFieldSelector);
    const autocompleteAddressForm = new AutocompletedAddressForm(
        dom('.address-autocomplete'),
        dom('.address-block'),
        new AddressObject(
            dom('#app_procuration_proposal_address'),
            dom('#app_procuration_proposal_postalCode'),
            dom('#app_procuration_proposal_cityName'),
            null,
            dom('#app_procuration_proposal_country')
        ),
        dom('#address-autocomplete-help-message')
    );

    autocompleteAddressForm.once('changed', () => {
        countryElement.dispatchEvent(new CustomEvent('change', {
            target: countryElement,
        }));
    });

    autocompleteAddressForm.buildWidget();

    changeFieldsVisibility(countryElement, postalCodeFieldSelector, stateFieldSelector);
};
