import validateEmail from '../validator/emailValidator';
import formValidator from '../validator/formValidator';

export default (formType) => {
    const form = dom('form[name="adherent_registration"]') || dom('form[name="become_adherent"]');
    const emailField = dom('#adherent_registration_emailAddress_first');
    const confirmEmailField = dom('#adherent_registration_emailAddress_second');
    let zipCodeField = dom('#adherent_registration_address_postalCode');
    const captchaBlock = dom('div.g-recaptcha');

    if (!zipCodeField) {
        zipCodeField = dom('#become_adherent_address_postalCode');
    }

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

    /**
     * Display captcha block when the ZipCode is filled and remove the listener from ZipCode field
     *
     * @param event
     */
    const displayCaptcha = (event) => {
        if (captchaBlock
            && event.target.value
            && -1 !== captchaBlock.parentElement.className.indexOf('visually-hidden')
        ) {
            removeClass(captchaBlock.parentElement, 'visually-hidden');
            off(zipCodeField, 'input', displayCaptcha);
        }
    };

    if (emailField) {
        on(emailField, 'input', checkEmail);
        emailField.dispatchEvent(new Event('input'));
    }

    on(zipCodeField, 'input', displayCaptcha);
    zipCodeField.dispatchEvent(new Event('input'));

    formValidator(formType, form);
};
