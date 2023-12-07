// type xReIcon with jsdoc as Alpine.data second argument
/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * @param {{
 * steps: string[],
 * onChange: (value: number) => void,
 * id: string,
 * initStep: number,
 * }} props
 * @returns {AlpineComponent}
 */
const xReStepper = (props) => ({
    ...props,
    currentStep: props.initStep ?? 0,

    getProgress() {
        return this.currentStep + 1;
    },

    setCurrentStep(step) {
        this.currentStep = step;
    },

    handleStepClick(step) {
        this.currentStep = step;
        this.$dispatch(`stepper:${props.id}:change`, step);
    },

    /**
     * thatsStepIsCurrent
     * @param {number} step
     */
    isCurrent(step) {
        return this.currentStep === step;
    },

    /**
     * @param {number} step
     */
    isBeforeCurrent(step) {
        return this.currentStep > step;
    },

    /**
     * @param {number} step
     */
    isAfterCurrent(step) {
        return this.currentStep < step;
    },
});

export default {
    xReStepper,
};
