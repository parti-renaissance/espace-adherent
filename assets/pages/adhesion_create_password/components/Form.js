/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * @returns {AlpineComponent}
 */
const Form = () => ({
    fieldsValid: {
        password: false,
        passwordConfirmation: false,
    },
    isPasswordChanged: false,
    loading: false,

    setFieldValid(field) {
        return (value) => {
            this.fieldsValid[field] = value;
            return this.fieldsValid;
        };
    },

    handlePasswordInput(e) {
        this.isPasswordChanged = Boolean(e.target.value);
    },

    triggerValidateOnAllField() {
        this.$refs.form.querySelectorAll('input').forEach((x) => x.dispatchEvent(new Event('change')));
    },

    handleSubmit(e) {
        e.preventDefault();
        this.triggerValidateOnAllField();
        if (this.checkValidity()) {
            this.loading = true;
            this.$refs.form.submit();
        }
    },

    checkValidity() {
        return Object.values(this.fieldsValid).every((x) => x);
    },
});

export default Form;
