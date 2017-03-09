/*
 * Registration
 */
export default () => {
    const passTwo = dom('#membership_request_password_second');
    const passOne = dom('#membership_request_password_first');
    const checkPass = (event) => {
        if (passOne.value === passTwo.value) {
            passTwo.setCustomValidity('');
        } else {
            passTwo.setCustomValidity('Le mot de passe et sa confirmation sont différents');
        }
    };
    on(passOne, 'input', checkPass);
    on(passTwo, 'input', checkPass);
};
