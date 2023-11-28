/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import CommonFormStep from './CommonFormStep';

/**
 * First Step component for funnel
 * @returns {AlpineComponent}
 */
const SecondForm = () => ({
    ...CommonFormStep(),
    nextStepId: 'step_3',
    fieldsValid: {
        gender: false,
        lastName: false,
        firstName: false,
        nationality: true,
        address: false,
        postalCode: false,
        cityName: false,
    },

    async handleOnSubmit(e) {
        this._handleOnSubmitBase(e);
        this.handleNextStep();
    },
});

export default SecondForm;
