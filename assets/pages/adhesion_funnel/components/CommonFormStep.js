import reScrollTo from '../../../utils/scrollTo';
import { camelToSnakeCase, snakeToCamelCase } from '../../../utils/string';

/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

/**
 * First Step component for funnel
 * @returns {AlpineComponent}
 */
const CommonFormStep = () => ({
    fieldsValid: {},
    generalNotification: null,
    loading: false,
    nextStepId: '',
    id: '',
    stepData: null,

    setFieldValid(field) {
        return (value) => {
            this.fieldsValid[field] = value;
            return this.fieldsValid;
        };
    },

    triggerValidateOnAllField() {
        document.querySelectorAll(`#${this.id} input`).forEach((x) => x.dispatchEvent(new Event('change')));
    },

    isNotifResponse(payload) {
        return payload && payload.status && payload.message;
    },

    _handleBadRequest(data) {
        data.violations.forEach((x) => {
            const prop = x.propertyPath.startsWith('address') ? `address_${snakeToCamelCase(x.propertyPath)}` : snakeToCamelCase(x.propertyPath);
            this.$dispatch(`x-validate:membership_request_${prop}`, {
                status: data.status,
                message: x.message,
            });
        });
    },

    scrollToFirstError() {
        this.$nextTick(() => {
            const firstError = document.querySelector('[data-status="error"]');
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                    inline: 'nearest',
                });
            }
        });
    },

    saveFormToLocalStorage() {
        const form = document.querySelector('form[name="membership_request"]');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        localStorage.setItem('membership_request', JSON.stringify(data));
    },

    clearLocalStorage() {
        localStorage.removeItem('membership_request');
    },

    setStepData(namespaces = [], pipe = (x, y) => y) {
        const data = Array.from(document.querySelectorAll(`#${this.id} input, #${this.id} textarea, #${this.id} select`))
            .filter((x) => x.id.startsWith('membership_request_'))
            .reduce((acc, x) => {
                const [a, b, ...fieldnames] = x.id.split('_');
                const fieldname = fieldnames.join('_');
                if ('radio' === x.type) {
                    if (x.checked) {
                        acc[camelToSnakeCase(fieldnames[0])] = pipe(fieldnames[0], x.value);
                    }
                    return acc;
                }

                if ('checkbox' === x.type) {
                    acc[camelToSnakeCase(fieldnames[0])] = pipe(fieldnames[0], x.checked);
                    return acc;
                }

                if (namespaces.includes(fieldnames[0])) {
                    const [namespace, ..._name] = fieldnames;
                    const name = _name.join('_');
                    acc[namespace] = {
                        ...acc[namespace],
                        [camelToSnakeCase(name)]: pipe(name, x.value),
                    };
                    return acc;
                }
                acc[camelToSnakeCase(fieldname)] = pipe(fieldname, x.value);
                return acc;
            }, {});
        this.formData = { ...this.formData, ...data };
    },

    handleNextStep() {
        dom(`#${this.nextStepId}`).classList.remove('re-step--disabled');
        reScrollTo(this.nextStepId);
    },

    checkValidity() {
        return Object.values(this.fieldsValid).every((x) => x);
    },

    /**
     *
     * @param {MouseEvent} e
     * @return {boolean}
     * @private
     */
    _handleOnSubmitBase(e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            this.triggerValidateOnAllField();
            return false;
        }
        this.saveFormToLocalStorage();
        return true;
    },
});

export default CommonFormStep;
