import reScrollTo from '../../../utils/scrollTo';

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
    id: '',
    stepData: null,

    setFieldValid(field) {
        return (value) => {
            this.fieldsValid[field] = value;
            return this.fieldsValid;
        };
    },

    triggerValidateOnAllField() {
        document.querySelectorAll(`#${this.id} input`).forEach((x) => x.dispatchEvent(new Event('change')));
    },

    isNotifResponse(payload) {
        return payload && payload.status && payload.message;
    },

    scrollToFirstError() {
        this.$nextTick(() => {
            const firstError = document.querySelector('[data-status="error"]');
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                    inline: 'nearest',
                });
            }
        });
    },

    handleNextStep() {
        dom(`#${this.nextStepId}`).classList.remove('re-step--disabled');
        reScrollTo(this.nextStepId);
    },

    checkValidity() {
        return Object.values(this.fieldsValid).every((x) => x);
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
