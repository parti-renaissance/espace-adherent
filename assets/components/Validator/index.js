import { captureException } from '@sentry/browser';
import './typedef';

// @ts-check

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
 *
 * @param {ValidateType} type
 * @return {(bool: boolean, opt?: (Array<string>)) => ValidateTuple}
 */
function setSuccessOrError(type) {
    return (bool, opt) => [
        type, bool ? 'success' : `error${opt ? (`.${opt.join('.')}`) : ''}`,
    ];
}

/**
 * @param {ValidateType} type
 * @param {string|boolean} value
 * @param {SetNotifyState} cb
 * @returns {ValidateTuple}
 */
const isTypeConditionPassed = (type, value, cb) => {
    if ('function' === typeof type) {
        return type(value, cb);
    }

    const [typeLabel, ...options] = type.split(':');

    const successOrError = setSuccessOrError(typeLabel);

    if ('required' === typeLabel) {
        return successOrError(!!value);
    }
    if ('email' === typeLabel) {
        validateEmail(value)
            .then(cb);
        return /** @type {ValidateTuple} */ ['email', 'loading'];
    }
    if ('min' === typeLabel) {
        const [min] = options;
        if (!min) throw new Error('Missing min value');
        return successOrError(value.length >= Number(min), [min]);
    }

    if ('max' === typeLabel) {
        const [max] = options;
        if (!max) throw new Error('Missing max value');
        return successOrError(value.length <= Number(max), [max]);
    }

    if ('number' === typeLabel) {
        return successOrError(!Number.isNaN(Number(value)));
    }

    throw new Error(`Unknown type ${type}`);
};

const messageLib = {
    required: {
        error: 'Ce champ est requis.',
    },
    min: {
        error: (min) => `Ce champ doit contenir au moins ${min} caractères`,
    },
    max: {
        error: (max) => `Ce champ doit contenir au maximum ${max} caractères`,
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
    const [statusLabel, ...args] = status.split('.');
    const message = 0 < args.length ? messageLib[type][statusLabel](...args) : messageLib[type][statusLabel];
    if (!message) throw new Error(`Missing message for type ${type} and status ${status}`);
    return message;
};

/**
 * Check is first validate type is optional '?:callback' and rip it from the array
 * @param {ValidateType[]} types
 * @param {HTMLInputElement} el
 * @return {ValidateType[]}
 */
function useValidationOptional(types, el) {
    const [firstVType, ...tailVTypes] = types;
    if (!firstVType) return types;
    const [firstVLabel, callback] = firstVType.split(':');
    if ('?' === firstVLabel) {
        if (!callback) throw new Error('Missing callback for ? type');
        if (!window[callback]) throw new Error(`Unknown callback ${callback}`);
        const isOptional = window[callback]();
        if (isOptional) {
            if (tailVTypes.includes('required')) {
                el.toggleAttribute('required', false);
            }
            return [];
        }
        return tailVTypes;
    }
    return types;
}

function getValue(domEl) {
    const domType = domEl.getAttribute('type') || 'text';
    /** @type {string|boolean|null} */
    let value = null;
    switch (domType) {
        case 'checkbox':
            value = domEl.checked;
            break;
        case 'email':
        case 'text':
        case 'textarea':
        case 'number':
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
        case 'hidden':
            return;
        default:
            throw new Error(`Unknown type ${domType}`);
    }
    // eslint-disable-next-line consistent-return
    return value;
}

/**
 * @param { ValidateType[] } validateTypes
 * @param { HTMLInputElement } domEl
 * @param { SetNotifyState } setState
 */
const validateField = (validateTypes, domEl, setState) => {
    const value = getValue(domEl);

    /** @type {ValidateState} */
    const successState = {
        status: '' === value || 0 === validateTypes.length ? 'default' : 'valid',
        message: '',
    };

    const vTypes = useValidationOptional(validateTypes, domEl);

    const newState = vTypes.map((t) => isTypeConditionPassed(t, value, setState) ?? []);
    const path = newState.find(([, s]) => s.startsWith('error'))
        || newState.find(([, s]) => 'loading' === s)
        || newState.find(([, s]) => 'valid' === s)
        || undefined;
    if (!path) {
        setState(successState);
    } else {
        setState({
            status: path[1].split('.')[0],
            message: getStatusMessage(path[0], path[1]),
        });
    }
};

/** @param {ValidateState} state  */
const xValidate = (state) => ({
    ...state,
    init() {
        this.$watch('status', (status) => {
            this.$el.setAttribute('data-status', status);
        });

        this.$nextTick(() => {
            const els = this.$el.querySelectorAll('input, textarea, select');
            els.forEach((el) => {
                const value = getValue(el);
                el.setAttribute('data-tovalidate', true);
                if (value && 'default' === state.status) {
                    this.checkField({ currentTarget: el });
                }
            });
        });
    },
    setData(data) {
        this.status = data.status;
        this.message = data.message;
        if (this.onCheck) this.onCheck(!['error', 'loading'].includes(data.status));
    },
    checkField(e) {
        validateField(this.validate, e.currentTarget, this.setData.bind(this));
    },
    validateField: {
        // eslint-disable-next-line func-names
        '@change': function (e) {
            this.checkField(e);
        },
    },
});
export default {
    xValidate,
};
