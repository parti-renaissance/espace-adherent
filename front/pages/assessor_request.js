import AutocompletedAddressForm from '../services/address/AutocompletedAddressForm';
import AddressObject from '../services/address/AddressObject';

export default (countryFieldSelector) => {
    const countryElement = dom(countryFieldSelector);
    const autocompleteAddressForm = new AutocompletedAddressForm(
        dom('.address-autocomplete'),
        dom('.address-block'),
        new AddressObject(dom('#assessor_request_address'), dom('#assessor_request_postalCode'), dom('#assessor_request_city'), null, dom('#assessor_request_country')),
        dom('#address-autocomplete-help-message')
    );

    autocompleteAddressForm.once('changed', () => {
        countryElement.dispatchEvent(
            new CustomEvent('change', {
                target: countryElement,
            })
        );
    });

    autocompleteAddressForm.buildWidget();
};
