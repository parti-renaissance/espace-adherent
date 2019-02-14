import validateEmail from '../validator/emailValidator';
import formValidator from '../validator/formValidator';

export default (formType) => {
    const form = dom('form[name="user_registration"]');
    const emailField = dom('#user_registration_emailAddress_first');
    const confirmEmailField = dom('#user_registration_emailAddress_second');
    const zipCodeField = dom('#user_registration_address_postalCode');

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

    on(emailField, 'input', checkEmail);

    emailField.dispatchEvent(new Event('input'));
    zipCodeField.dispatchEvent(new Event('input'));

    formValidator(formType, form);
};
