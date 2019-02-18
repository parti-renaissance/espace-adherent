import validateEmail from '../validator/emailValidator';
import formValidator from '../validator/formValidator';
import AutocompletedAddressForm from '../services/address/AutocompletedAddressForm';
import AddressObject from '../services/address/AddressObject';

export default (formType) => {
    const form = dom('form[name="adherent_registration"]') || dom('form[name="become_adherent"]');
    const emailField = dom('#adherent_registration_emailAddress_first');
    const confirmEmailField = dom('#adherent_registration_emailAddress_second');
    let zipCodeField = dom('#adherent_registration_address_postalCode');

    if (!zipCodeField) {
        zipCodeField = dom('#become_adherent_address_postalCode');
    }

    const regionField = dom('#adherent_registration_address_region');
    const countryField = dom('#adherent_registration_address_country');
    const cityNameField = dom('#adherent_registration_address_cityName');

    hide(regionField);

    const address = new AddressObject(
        dom('#adherent_registration_address_address'),
        dom('#adherent_registration_address_postalCode'),
        cityNameField,
        regionField,
        countryField
    );

    const autocompleteAddressForm = new AutocompletedAddressForm(
        dom('.address-autocomplete'),
        dom('.address-block'),
        address
    );

    autocompleteAddressForm.once('changed', () => {
        countryField.dispatchEvent(new CustomEvent('change', {
            target: countryField,
            detail: {
                zipCode: zipCodeField.value,
                cityName: cityNameField.value,
            },
        }));
    });

    autocompleteAddressForm.buildWidget();

    /**
     * Display/hide the second email field according the value of first email field
     *
     * @param event
     */
    const checkEmail = (event) => {
        if (validateEmail(event.target.value)) {
            removeClass(confirmEmailField.parentElement, 'visually-hidden');
        } else {
            addClass(confirmEmailField.parentElement, 'visually-hidden');
        }
    };

    if (emailField) {
        on(emailField, 'input', checkEmail);
        emailField.dispatchEvent(new Event('input'));
    }

    zipCodeField.dispatchEvent(new Event('input'));

    formValidator(formType, form);
};
