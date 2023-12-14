/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * @param {{email: string}} props
 * @returns {AlpineComponent}
 */
const Form = (props) => ({
    fieldsValid: {
        code: false,
    },
    isChangeMailMode: false,
    isMailChanged: false,
    loading: false,

    setFieldValid(field) {
        return (value) => {
            this.fieldsValid[field] = value;
            return this.fieldsValid;
        };
    },
    handleCodeInput(e) {
        const { target } = e;
        // replace spaces and non numeric characters by empty string
        target.value = target.value.replace(/[^0-9]/g, '');
        if (4 === target.value.length || 4 < target.value.length) {
            target.dispatchEvent(new Event('change'));
        }
        target.dispatchEvent(new Event('input'));
    },

    handleEmailInput(e) {
        const { target } = e;
        if (target.value !== props.email) {
            this.isMailChanged = true;
        } else {
            this.isMailChanged = false;
        }
    },

    triggerValidateOnAllField() {
        document.querySelectorAll(`#${this.id} input`)
            .forEach((x) => x.dispatchEvent(new Event('change')));
    },

    checkValidity() {
        return this.isChangeMailMode ? this.fieldsValid.email : this.fieldsValid.code;
    },

});

export default Form;
