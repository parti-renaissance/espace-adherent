/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * First Step component for funnel
 * @returns {AlpineComponent}
 */
const CommonFormStep = () => ({
    fieldsValid: {},
    generalNotification: null,
    loading: false,
    nextStepId: '',

    setFieldValid(field) {
        return (value) => {
            this.fieldsValid[field] = value;
            return this.fieldsValid;
        };
    },

    triggerValidateOnAllField() {
        this.$el.querySelectorAll('input')
            .forEach((x) => x.dispatchEvent(new Event('change')));
    },

    handleNextStep() {
        dom(`#${this.nextStepId}`)
            .scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest',
            });
    },
    checkValidity() {
        return Object.values(this.fieldsValid)
            .every((x) => x);
    },

    /**
     *
     * @param {MouseEvent} e
     * @return {boolean}
     * @private
     */
    _handleOnSubmitBase(e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            this.triggerValidateOnAllField();
            return false;
        }
        return true;
    },
});

export default CommonFormStep;
