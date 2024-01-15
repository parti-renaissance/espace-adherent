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
    fieldsValid: {
        autorisations: false,
        captcha: false,
    },
    captchaToken: null,
    loading: false,
    handleOnSubmit(e) {
        this.loading = true;
    },

    init() {
        this.$nextTick(() => {
            const tokenInput = dom('input[name="frc-captcha-solution"]:last-child');

            if (dom('.frc-captcha')) {
                friendlyChallenge.autoWidget.opts.doneCallback = (token) => {
                    this.captchaToken = token;
                    this.fieldsValid.captcha = true;
                };
            } else if (tokenInput && tokenInput.value) {
                this.captchaToken = tokenInput.value;
                this.fieldsValid.captcha = true;
            }
        });
    },
});

export default ThirdForm;
