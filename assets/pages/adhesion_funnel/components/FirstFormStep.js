/** @typedef  {import('alpinejs').AlpineComponent} AlpineComponent */

import { captureException } from '@sentry/browser';
import CommonFormStep from './CommonFormStep';

/**
 * First Step component for funnel
 * @returns {AlpineComponent}
 */
const FirstForm = () => ({
    ...CommonFormStep(),
    fieldsValid: {
        email: false,
        consentDataCollect: false,
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
        const emailInput = document.querySelector('#membership_request_email');
        const consentDataCollectInput = document.querySelector('#membership_request_consentDataCollect');
        if (emailInput.value && consentDataCollectInput.checked) {
            this.submitted = true;
            this.submittedValues = {
                email: emailInput.value,
                consentDataCollect: consentDataCollectInput.checked,
            };
        }
        emailInput.addEventListener('change', this.checkIdFieldChangeAfterSubmit('email'));
        consentDataCollectInput.addEventListener('change', this.checkIdFieldChangeAfterSubmit('consentDataCollect'));

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
        return fetch('/api/persist-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: document.querySelector('#membership_request_email').value,
                recaptcha: this.captchaToken,
                utm_source: document.querySelector('#membership_request_utmSource').value,
                utm_campaign: document.querySelector('#membership_request_utmCampaign').value,
            }),
        });
    },

    _handleBadRequest($dispatch) {
        return (data) =>
            data.violations.forEach((x) => {
                if ('email' === x.propertyPath) {
                    $dispatch('x-validate:membership_request_email', {
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
                        this.setStepData();
                        this.submitted = true;
                        this.submittedValues = {
                            email: document.querySelector('#membership_request_email').value,
                            consentDataCollect: document.querySelector('#membership_request_consentDataCollect').checked,
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
