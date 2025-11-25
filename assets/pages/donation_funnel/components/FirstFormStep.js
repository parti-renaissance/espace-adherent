/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

import CommonFormStep from './CommonFormStep';

function closest(num, arr) {
    let curr = arr[0];
    let diff = Math.abs(num - curr);
    for (let val = 0; val < arr.length; val += 1) {
        const newdiff = Math.abs(num - arr[val]);
        if (newdiff < diff) {
            diff = newdiff;
            curr = arr[val];
        }
    }
    return curr;
}

/**
 * First Step component for funnel
 * @returns {AlpineComponent}
 */
const FirstForm = ({ amounts: uniqAmounts = [30, 60, 120, 250, 500, 1000] } = {}) => {
    const monthlyAmounts = [5, 10, 20, 30, 60, 100];
    return {
        ...CommonFormStep(),
        fieldsValid: {},
        nextStepId: 'step_2',
        id: 'step_1',
        submitted: false,
        submittedValues: null,
        defaultCustomAmount: '',

        getTaxTextReduction() {
            return `${(this.amount * 0.34).toFixed(2).toLocaleString()} € ${'-1' === this.duration ? '/ mois' : ''}`;
        },

        handleAmountClick(amount) {
            this.animateSvg(amount);
            this.amount = amount;
            document.querySelector('#amount_custom').value = '';
        },

        /**
         * @param {Event} e
         */
        handleCustomFieldChange(e) {
            const { value } = e.target;
            this.amount = '' === value ? 60 : value;
            this.animateSvg(this.amount);
        },

        animateSvg(value) {
            let index = this.getAmounts().indexOf(Number(value));
            if (-1 === index) {
                index = this.getAmounts().indexOf(closest(Number(value), this.getAmounts()));
            }
            const indexesToAnimate = Array.from(Array(index + 1).keys());
            document.querySelectorAll('[id^="p_"]').forEach((el) => {
                const elIndex = Number(el.id.split('_')[1]) - 1;
                if (indexesToAnimate.includes(elIndex)) return;
                el.classList.remove('active');
            });
            indexesToAnimate.forEach((i) => {
                setTimeout(() => {
                    document.querySelector(`#p_${i + 1}`).classList.add('active');
                }, i * 100);
            });
        },

        init() {
            this.animateSvg(this.amount);
            this.defaultCustomAmount = this.getCustomAmount();
        },

        getAmounts() {
            return '0' === this.duration ? uniqAmounts : monthlyAmounts;
        },
        getCustomAmount() {
            return uniqAmounts.includes(Number(this.amount)) ? '' : this.amount;
        },
        async handleOnSubmit(e) {
            e.preventDefault();
            this.handleNextStep();
        },
    };
};

export default FirstForm;
