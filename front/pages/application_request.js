import validateEmail from '../validator/emailValidator';
import formValidator from '../validator/formValidator';
import AutocompletedAddressForm from '../services/address/AutocompletedAddressForm';
import AddressObject from '../services/address/AddressObject';

export default (volunteerFormType, runningMateFormType) => {
    /**
     * Volunteer request form
     */
    const volunteerForm = dom('form[name="volunteer_request"]');
    const volunteerRootIdField = `#${volunteerForm.name}`;

    const volunteerCountryField = dom(`${volunteerRootIdField}_address_country`);
    const volunteerCityNameField = dom(`${volunteerRootIdField}_address_cityName`);
    const volunteerZipCodeField = dom(`${volunteerRootIdField}_address_postalCode`);

    const volunteerAddress = new AddressObject(
        dom(`${volunteerRootIdField}_address_address`),
        volunteerZipCodeField,
        volunteerCityNameField,
        null,
        volunteerCountryField
    );

    const volunteerAutocompleteAddressForm = new AutocompletedAddressForm(
        dom('form[name="volunteer_request"] .address-autocomplete'),
        dom('form[name="volunteer_request"] .address-block'),
        volunteerAddress
    );

    volunteerAutocompleteAddressForm.once('changed', () => {
        volunteerCountryField.dispatchEvent(new CustomEvent('change', {
            target: volunteerCountryField,
            detail: {
                zipCode: volunteerZipCodeField.value,
                cityName: volunteerCityNameField.value,
            },
        }));
    });

    volunteerAutocompleteAddressForm.buildWidget();

    volunteerZipCodeField.dispatchEvent(new Event('input'));

    formValidator(volunteerFormType, volunteerForm);


    /**
     * Running mate request form
     */
    const runningMateForm = dom('form[name="running_mate_request"]');
    const runningMateRootIdField = `#${runningMateForm.name}`;

    const runningMateCountryField = dom(`${runningMateRootIdField}_address_country`);
    const runningMateCityNameField = dom(`${runningMateRootIdField}_address_cityName`);
    const runningMateZipCodeField = dom(`${runningMateRootIdField}_address_postalCode`);

    const runningMateAddress = new AddressObject(
        dom(`${runningMateRootIdField}_address_address`),
        runningMateZipCodeField,
        runningMateCityNameField,
        null,
        runningMateCountryField
    );

    const runningMateAutocompleteAddressForm = new AutocompletedAddressForm(
        dom('form[name="running_mate_request"] .address-autocomplete'),
        dom('form[name="running_mate_request"] .address-block'),
        runningMateAddress
    );

    runningMateAutocompleteAddressForm.once('changed', () => {
        runningMateCountryField.dispatchEvent(new CustomEvent('change', {
            target: runningMateCountryField,
            detail: {
                zipCode: runningMateZipCodeField.value,
                cityName: runningMateCityNameField.value,
            },
        }));
    });

    runningMateAutocompleteAddressForm.buildWidget();

    runningMateZipCodeField.dispatchEvent(new Event('input'));

    formValidator(runningMateFormType, runningMateForm);
};
