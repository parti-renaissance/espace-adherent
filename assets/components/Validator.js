import { captureException } from '@sentry/browser';

// @ts-check
/** @typedef {'error'|'warning'|'valid'|'info'|'default'} ValidateStatus */
/** @typedef {'required'|'email'} ValidateType */
/** @typedef {{
 * status:ValidateStatus,
 * message:string,
 * validate:ValidateType,
 * onCheck?: (x:boolean)=>void
 * }} ValidateState */
/** @typedef {[ValidateType, ValidateState]} ValidateTuple */

/**
 * Check if body payload is correct
 * @param {unknown} payload
 * @returns {boolean}
 */
const hasCorrectPayload = (payload) => {
    if ('OK' === payload) return true;
    if ('object' !== typeof payload) return false;
    if (!Object.prototype.hasOwnProperty.call(payload, 'status')) return false;
    return Object.prototype.hasOwnProperty.call(payload, 'message');
};

const validateEmail = (email) => {
    /** @type {string|undefined} */
    const token = dom('#email-validation-token').value;
    if (!token) throw new Error('Missing email validation token');

    return fetch('/api/validate-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            email,
            token,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (!hasCorrectPayload(data)) {
                captureException(new Error('Invalid payload from /api/validate-email'), {
                    extra: { data },
                });
            }
            return 'OK' === data
                ? {
                    status: 'success',
                    message: '',
                }
                : data;
        })
        .catch((error) => {
            captureException(error);
            return {
                status: 'error',
                message: 'Une erreur est survenue lors de la validation de votre email',
            };
        });
};

/**
 * @param {ValidateType} type
 * @param {string|boolean} value
 * @param {(x:ValidateState)=>void} cb
 * @returns {ValidateTuple}
 */
const isTypeConditionPassed = (type, value, cb) => {
    switch (type) {
    case 'required': {
        return /** @type {ValidateTuple} */ value ? ['required', 'success'] : ['required', 'error'];
    }
    case 'email':
        validateEmail(value)
            .then(cb);
        return /** @type {ValidateTuple} */ ['email', 'loading'];
    }
    throw new Error(`Unknown type ${type}`);
};

const messageLib = {
    required: {
        error: 'Ce champ est requis',
    },
    email: {
        loading: 'Vérification de votre email...',
    },
};

/**
 * @param {ValidateType} type
 * @param {ValidateStatus} status
 */
const getStatusMessage = (type, status) => {
    const message = messageLib[type][status];
    if (!message) throw new Error(`Missing message for type ${type} and status ${status}`);
    return message;
};

/**
 * @param { ValidateType[] } validateTypes
 * @param { HTMLInputElement } domEl
 * @param { (x:ValidateState)=>void } setState
 */
const validateField = (validateTypes, domEl, setState) => {
    const domType = domEl.getAttribute('type') || 'text';
    /** @type {string|boolean|null} */
    let value = null;
    switch (domType) {
    case 'checkbox':
        value = domEl.checked;
        break;
    case 'email':
    case 'text':
    case 'password':
        value = domEl.value;
        break;
    case 'radio':
    case 'radio-group': {
        const name = domEl.getAttribute('name');
        if (!name) throw new Error('Missing name attribute');
        const allRadios = document.querySelectorAll(`input[name="${name}"]`);
        const radios = [...allRadios].find((r) => r.checked);
        value = radios ? radios.value : null;
    }
        break;
    default:
        throw new Error(`Unknown type ${domType}`);
    }
    /** @type {ValidateState} */
    const successState = {
        status: 'valid',
        message: '',
    };
    const newState = validateTypes.map((t) => isTypeConditionPassed(t, value, setState) ?? []);
    const path = newState.find(([, s]) => 'error' === s)
        || newState.find(([, s]) => 'loading' === s)
        || newState.find(([, s]) => 'valid' === s)
        || undefined;
    if (!path) {
        setState(successState);
    } else {
        setState({
            status: path[1],
            message: getStatusMessage(path[0], path[1]),
        });
    }
};

/** @param {ValidateState} state  */
const xValidate = (state) => ({
    ...state,
    setData(data) {
        this.status = data.status;
        this.message = data.message;
        if (this.onCheck) this.onCheck(['valid', 'success', 'warning'].includes(data.status));
    },
    checkField(e) {
        validateField(this.validate, e.currentTarget, this.setData.bind(this));
    },
    validateField: {
        // eslint-disable-next-line func-names
        '@change': function (e) {
            this.checkField(e);
        },
        // eslint-disable-next-line func-names
        '@blur': function (e) {
            this.checkField(e);
        },
    },
});
export default {
    xValidate,
};
