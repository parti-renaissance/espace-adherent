/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * First Step component for funnel
 * @returns {AlpineComponent}
 */
const Page = () => ({
    accessibility: false,
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

    handleAccessibilityChange(e) {
        this.accessibility = e.target.checked;
        if (false === this.accessibility) {
            document.querySelector('#campus_event_inscription_accessibility').value = '';
        }
    },

    fieldsValid: {
        email: false,
        acceptCgu: false,
        acceptMedia: false,
        captcha: false,
        gender: false,
        lastName: false,
        firstName: false,
        postalCode: false,
        birthDay: false,
        birthMonth: false,
        birthYear: false,
    },
    captchaToken: null,

    setFieldValid(field) {
        return (value) => {
            this.fieldsValid[field] = value;
            return this.fieldsValid;
        };
    },

    checkValidity() {
        return Object.values(this.fieldsValid)
            .every((x) => x);
    },
});

export default Page;
