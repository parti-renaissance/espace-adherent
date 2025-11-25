/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

import { captureException } from '@sentry/browser';
import CommonFormStep from './CommonFormStep';

/**
 * First Step component for funnel
 * @param {{ api: string }} props
 * @returns {AlpineComponent}
 */
const FirstForm = (props) => ({
    ...CommonFormStep(),
    fieldsValid: {
        email: false,
        acceptCgu: false,
        captcha: false,
    },
    captchaToken: null,
    nextStepId: 'step_2',
    id: 'step_1',
    submitted: false,
    submittedValues: null,

    checkIdFieldChangeAfterSubmit(field) {
        return (e) => {
            if (this.submitted) {
                const input = e.target;
                if ('checkbox' === input.type ? input.checked : input.value !== this.submittedValues[field]) {
                    this.submitted = false;
                    this.submittedValues = null;
                    this.fieldsValid[field] = false;
                    this.stepToFill = 10;
                    this.stepToFill = 0;
                }
            }
        };
    },

    init() {
        const emailInput = document.querySelector('[id$=_email]');
        const acceptCguInput = document.querySelector('[id$=_acceptCgu]');
        if (emailInput.value && acceptCguInput.checked) {
            this.submitted = true;
            this.submittedValues = {
                email: emailInput.value,
                acceptCgu: acceptCguInput.checked,
            };
        }
        emailInput.addEventListener('change', this.checkIdFieldChangeAfterSubmit('email'));
        acceptCguInput.addEventListener('change', this.checkIdFieldChangeAfterSubmit('acceptCgu'));

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

    async _postPersistEmail() {
        const params = new URLSearchParams(window.location.search);
        return fetch(props.api, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: document.querySelector('[id$=_email]').value,
                recaptcha: this.captchaToken,
                type: document.querySelector('#procuration_proxy_email') ? 'proxy' : 'request',
                utm_source: params.get('utm_source'),
                utm_campaign: params.get('utm_campaign'),
            }),
        });
    },

    _handleBadRequest($dispatch) {
        return (data) =>
            data.violations.forEach((x) => {
                if ('email' === x.propertyPath) {
                    const proxyOrRequest = document.querySelector('#procuration_proxy_email') ? 'proxy' : 'request';
                    $dispatch(`x-validate:procuration_${proxyOrRequest}_email`, {
                        status: data.status,
                        message: x.message,
                    });
                }

                if ('recaptcha' === x.propertyPath) {
                    this.captchaToken = null;
                    this.fieldsValid.captcha = false;
                    this.generalNotification = {
                        status: data.status,
                        message: x.message,
                    };
                }
            });
    },

    async handleOnSubmit(e, $dispatch) {
        if (!this._handleOnSubmitBase(e)) {
            return new Promise(() => {});
        }

        this.loading = true;
        return this._postPersistEmail()
            .then((response) => response.json())
            .then((payload) => {
                if (this.isNotifResponse(payload)) {
                    if ('success' === payload.status) {
                        this.generalNotification = null;
                        this.handleNextStep();
                        this.submitted = true;
                        this.submittedValues = {
                            email: document.querySelector('[id$=_email]').value,
                            cgu: document.querySelector('[id$=_acceptCgu]').checked,
                        };
                        return;
                    }
                    if (payload.violations) {
                        this._handleBadRequest($dispatch)(payload);
                        this.scrollToFirstError();
                        return;
                    }
                    this.generalNotification = payload;
                    if ('error' === payload.status) {
                        this.scrollToFirstError();
                    }
                } else {
                    throw new Error('Invalid payload from /api/persist-email');
                }
            })
            .catch((error) => {
                this.generalNotification = {
                    status: 'error',
                    message: 'Une erreur est survenue lors de la validation de votre email',
                };
                this.scrollToFirstError();
                captureException(error, {
                    tags: {
                        component: 'membership-request',
                        step: 'persist-email',
                    },
                });
            })
            .finally(() => {
                this.loading = false;
            });
    },
});

export default FirstForm;
