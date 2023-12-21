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

});

export default FourthForm;
