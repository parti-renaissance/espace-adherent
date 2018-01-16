import validateEmail from '../validator/emailValidator';

export default () => {
    const emailField = dom('#new_member_ship_request_emailAddress_first');
    const confirmEmailField = dom('#new_member_ship_request_emailAddress_second');
    const zipCodeField = dom('#new_member_ship_request_address_postalCode');
    const captchaBlock = dom('div.g-recaptcha');

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
     * Display captcha block when the ZipCode is filled
     * and remove the listener from ZipCode field
     *
     * @param event
     */
    const displayCaptcha = (event) => {
        if (event.target.value && -1 !== captchaBlock.parentElement.className.indexOf('visually-hidden')) {
            removeClass(captchaBlock.parentElement, 'visually-hidden');
            off(zipCodeField, 'input', displayCaptcha);
        }
    };

    on(emailField, 'input', checkEmail);
    on(zipCodeField, 'input', displayCaptcha);

    emailField.dispatchEvent(new Event('input'));
    zipCodeField.dispatchEvent(new Event('input'));
};
