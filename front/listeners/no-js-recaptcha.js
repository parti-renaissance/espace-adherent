/*
 * Remove no-js Recaptcha inputs
 */
export default () => {
    findAll(document, '.nojs-g-recaptcha-response').forEach((element) => {
        remove(element);
    });
};
