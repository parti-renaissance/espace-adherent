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
    checkFormValidity(e) {
        const checkbox = document.getElementById('isStudent');
        if ('4' === this.value && !this.confirmStudent) {
            e.preventDefault();
            checkbox.scrollIntoView();
            checkbox.focus();
            checkbox.dispatchEvent(new Event('change'));
            return false;
        }
        return true;
    },
    submitForm(event) {
        if (!this.checkFormValidity(event)) return;
        this.loading = true;
    },

    calcCotisTotal() {
        const cotis = 30 <= this.price ? 30 : 10;
        const don = 10 === cotis ? 0 : this.price - cotis;
        const total = don + cotis;
        if (7500 < total) {
            return {
                cotis,
                don: don - (total - 7500),
                total: 7500,
            };
        }
        return {
            cotis,
            don,
            total,
        };
    },
});

export default FourthForm;
