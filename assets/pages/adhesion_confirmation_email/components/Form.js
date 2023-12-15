/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * @param {{email: string, step: string}} props
 * @returns {AlpineComponent}
 */
const Form = ({ email, step }) => ({
    fieldsValid: {
        code: false,
        email: false,
        confirmEmail: false,
    },
    isChangeMailMode: 'email' === step,
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
        // replace spaces and non-numeric characters by empty string
        target.value = target.value.replace(/[^0-9]/g, '');
        if (4 === target.value.length || 4 < target.value.length) {
            target.dispatchEvent(new Event('change'));
        }
        target.dispatchEvent(new Event('input'));
    },

    handleEmailInput(e) {
        this.isMailChanged = e.target.value !== email;
    },

    handleSubmit(e) {
        e.preventDefault();
        this.triggerValidateOnAllField();
        if (this.checkValidity()) {
            this.loading = true;
            this.$refs.form.submit();
        }
    },

    triggerValidateOnAllField() {
        this.$refs.form.querySelectorAll('input')
            .forEach((x) => x.dispatchEvent(new Event('change')));
    },

    checkValidity() {
        return this.isChangeMailMode
            ? this.fieldsValid.email && this.fieldsValid.confirmEmail
            : this.fieldsValid.code;
    },
});

export default Form;
