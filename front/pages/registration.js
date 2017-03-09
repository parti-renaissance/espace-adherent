/*
 * Registration
 */
export default () => {
    const passTwo = dom('#membership_request_password_second');
    const passOne = dom('#membership_request_password_first');
    const message = dom('#confirm-message');
    const okColor = '#016ea2';
    const notOkColor = '#90100c';

    on(passOne, 'input', (event) => {
        if ('' !== passTwo.value) {
            if (passOne.value === passTwo.value) {
                passOne.style.backgroundColor = okColor;
                passTwo.style.backgroundColor = okColor;
                message.style.color = okColor;
                message.innerHTML = 'Les mots de passe correspondent!';
            } else {
                passOne.style.backgroundColor = notOkColor;
                passTwo.style.backgroundColor = notOkColor;
                message.style.color = notOkColor;
                message.innerHTML = 'Les mots de passe ne correspondent pas !';
            }
        }
    });

    on(passTwo, 'input', (event) => {
        if ('' !== passOne.value) {
            if (passOne.value === passTwo.value) {
                passOne.style.backgroundColor = okColor;
                passTwo.style.backgroundColor = okColor;
                message.style.color = okColor;
                message.innerHTML = 'Les mots de passe correspondent!';
            } else {
                passOne.style.backgroundColor = notOkColor;
                passTwo.style.backgroundColor = notOkColor;
                message.style.color = notOkColor;
                message.innerHTML = 'Les mots de passe ne correspondent pas !';
            }
        }
    });
};
