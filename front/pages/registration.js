/*
 * Registration
 */
export default () => {
    const passTwo = dom('#membership_request_password_second');
    const passOne = dom('#membership_request_password_first');

    on(passOne, 'input', (event) => {
        if (passOne.value === passTwo.value) {
            passTwo.setCustomValidity('');
        } else {
            passTwo.setCustomValidity('Les mots de passe ne correspondent pas !');
        }
    });
    on(passTwo, 'input', (event) => {
        if (passOne.value === passTwo.value) {
            passTwo.setCustomValidity('');
        } else {
            passTwo.setCustomValidity('Les mots de passe ne correspondent pas !');
        }
    });
};
