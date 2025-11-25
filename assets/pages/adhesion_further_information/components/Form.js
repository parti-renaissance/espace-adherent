/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * @returns {AlpineComponent}
 */
const Form = ({ isJam = false, isElu = false }) => ({
    fieldsValid: {
        birthDay: false,
        birthMonth: false,
        birthYear: false,
    },
    isElu,
    isJam,
    loading: false,
    year: null,
    day: null,
    month: null,

    init() {
        this.$nextTick(() => {
            // get all fields with validate attribute
            const fields = document.querySelectorAll('input[validate]');
            // set all is inside this.fieldsValid to false
            fields.forEach((field) => {
                this.fieldsValid[field.id] = false;
            });
        });
    },

    setFieldValid(field) {
        return (value) => {
            this.fieldsValid[field] = value;
            return this.fieldsValid;
        };
    },

    setYear(value) {
        this.year = value;
        this.setIsJam();
    },

    setDay(value) {
        this.day = value;
        this.setIsJam();
    },

    setMonth(value) {
        this.month = value;
        this.setIsJam();
    },

    setIsJam() {
        if (this.day && this.month && this.year) {
            const date = new Date(this.year, this.month, this.day);
            // if less than 35 years old
            this.isJam = date.getTime() > Date.now() - 35 * 365 * 24 * 60 * 60 * 1000;
            return;
        }
        this.isJam = false;
    },

    handleIsElu(e) {
        this.isElu = Boolean(e.target.checked);
        if (!e.target.checked) {
            this.clearEluCheckboxes();
        }
    },

    clearEluCheckboxes() {
        document.querySelectorAll('#elu-form input:checked').forEach((x) => {
            x.checked = false;
        });
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
