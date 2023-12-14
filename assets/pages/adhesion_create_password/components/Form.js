/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * @param {{email: string}} props
 * @returns {AlpineComponent}
 */
const Form = (props) => ({
    fieldsValid: {
        code: false,
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
        this.isPasswordChanged = e.target.value !== props.email;
    },

    triggerValidateOnAllField() {
        document.querySelectorAll(`#${this.id} input`)
            .forEach((x) => x.dispatchEvent(new Event('change')));
    },

    checkValidity() {
        return Object.values(this.fieldsValid)
            .every((x) => x);
    },

});

export default Form;
