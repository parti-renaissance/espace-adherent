import formValidator from '../validator/formValidator';
import GooglePlaceAutocomplete from '../services/address/GooglePlaceAutocomplete';

export default (formType) => {
    formValidator(formType, dom('form[name="app_donation"]'));

    const autocomplete = new GooglePlaceAutocomplete(
        dom('.address-autocomplete'),
        dom('#app_donation_address'),
        dom('#app_donation_cityName'),
        dom('#app_donation_postalCode'),
        dom('#app_donation_country'),
        'form form--full form__field'
    );
    autocomplete.build();
};
