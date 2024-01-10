/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */
import '../../../components/Validator/typedef';
import CommonFormStep from './CommonFormStep';

const snakeToCamel = (str) => str.toLowerCase()
    .replace(/([-_][a-z])/g, (group) => group
        .replace('-', '')
        .replace('_', ''));

/**
 * First Step component for funnel
 * @return {AlpineComponent}
 */
const ThirdForm = () => ({
    ...CommonFormStep(),
    id: 'step_3',
    fieldsValid: {},
    handleOnSubmit(e) {
        this._handleOnSubmitBase(e);
    },
});

export default ThirdForm;
