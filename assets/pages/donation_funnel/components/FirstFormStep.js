/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

import CommonFormStep from './CommonFormStep';

/**
 * First Step component for funnel
 * @param {{amount: string, duration: string}} props
 * @returns {AlpineComponent}
 */
const FirstForm = (props) => {
    const uniqAmounts = [30, 60, 120, 250, 500, 1000];
    const monthlyAmounts = [5, 10, 20, 30, 60, 100];
    return ({
        ...CommonFormStep(),
        fieldsValid: {},
        ...props,
        nextStepId: 'step_2',
        id: 'step_1',
        submitted: false,
        submittedValues: null,

        getTaxTextReduction() {
            return `${(this.amount * 0.66).toFixed(2)} € ${'0' === this.duration ? '/ mois' : ''}`;
        },

        getAmounts() {
            return '0' === this.duration ? monthlyAmounts : uniqAmounts;
        },

        async handleOnSubmit(e, $dispatch) {
            e.preventDefault();
            this.handleNextStep();
        },
    });
};

export default FirstForm;
