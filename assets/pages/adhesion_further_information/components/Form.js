/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * @returns {AlpineComponent}
 */
const Form = () => ({
    fieldsValid: {},
    isElu: false,
    loading: false,

    init() {
        // get all fields with validate attribute
        const fields = this.$refs.form.querySelectorAll('[validate]');
        // set all is inside this.fieldsValid to false
        fields.forEach((field) => {
            this.fieldsValid[field.id] = false;
        });

        console.log(this.fieldsValid);
    },

    setFieldValid(field) {
        return (value) => {
            this.fieldsValid[field] = value;
            return this.fieldsValid;
        };
    },

    handleIsElu(e) {
        this.isElu = Boolean(e.target.checked);
    },

    handlePasswordInput(e) {
        this.isPasswordChanged = Boolean(e.target.value);
    },

    triggerValidateOnAllField() {
        this.$refs.form.querySelectorAll('input')
            .forEach((x) => x.dispatchEvent(new Event('change')));
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
        return Object.values(this.fieldsValid)
            .every((x) => x);
    },

});

export default Form;
