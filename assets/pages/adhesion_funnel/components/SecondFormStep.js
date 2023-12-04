/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import CommonFormStep from './CommonFormStep';

/**
 * First Step component for funnel
 * @returns {AlpineComponent}
 */
const SecondForm = () => ({
    ...CommonFormStep(),
    nextStepId: 'step_3',
    id: 'step_2',
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
        if (!this._handleOnSubmitBase(e)) {
            return;
        }
        this.handleNextStep();
    },
});

export default SecondForm;
