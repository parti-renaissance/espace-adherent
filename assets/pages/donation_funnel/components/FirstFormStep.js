/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

import CommonFormStep from './CommonFormStep';

/**
 * First Step component for funnel
 * @returns {AlpineComponent}
 */
const FirstForm = () => {
    const uniqAmounts = [30, 60, 120, 250, 500, 1000];
    const monthlyAmounts = [5, 10, 20, 30, 60, 100];
    return ({
        ...CommonFormStep(),
        fieldsValid: {},
        nextStepId: 'step_2',
        id: 'step_1',
        submitted: false,
        submittedValues: null,

        getTaxTextReduction() {
            return `${(this.amount * 0.66).toFixed(2)} € ${'-1' === this.duration ? '/ mois' : ''}`;
        },

        getAmounts(duration) {
            return '0' === this.duration ? uniqAmounts : monthlyAmounts;
        },
        getCustomAmount() {
            return uniqAmounts.includes(Number(this.amount)) ? '' : this.amount;
        },
        async handleOnSubmit(e, $dispatch) {
            e.preventDefault();
            this.handleNextStep();
        },
    });
};

export default FirstForm;
