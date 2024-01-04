/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import '../../../components/Validator/typedef';
import CommonFormStep from './CommonFormStep';

/**
 * First Step component for funnel
 * @param {{missingYears: number}} props
 * @return {AlpineComponent}
 */
const FourthForm = (props) => ({
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

    calcCotisTotal() {
        const cotis = 30 <= this.price ? 30 : 10;
        const don = 10 === cotis ? 0 : this.price - cotis;
        const totalCotis = props.missingYears ? props.missingYears * cotis : cotis;
        const total = don + totalCotis;
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
