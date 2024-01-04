/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import '../../../components/Validator/typedef';
import CommonFormStep from './CommonFormStep';

/**
 * First Step component for funnel
 * @return {AlpineComponent}
 */
const FourthForm = () => ({
    ...CommonFormStep(),
    value: '2',
    price: 60,
    isSelected(value) {
        return this.value === value;
    },
    confirmStudent: false,
    submitForm(event) {
        const checkbox = document.getElementById('isStudent');
        if ('4' === this.value && !this.confirmStudent) {
            event.preventDefault();
            checkbox.scrollIntoView();
            checkbox.focus();
            checkbox.dispatchEvent(new Event('change'));
            return;
        }
        this.loading = true;
    },

    calcCotisTotal(price, missingYears) {
        const don = 30 > price ? 0 : price - 30;
        const missing = missingYears * 30;
        const total = don + missing;
        if (7500 < total) {
            return {
                don: don - (total - 7500),
                total: 7500,
            };
        }
        return {
            don,
            total,
        };
    },
});

export default FourthForm;
