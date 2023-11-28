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

    init() {
        this.$nextTick(() => {
            // eslint-disable-next-line no-undef
            friendlyChallenge.autoWidget.opts.doneCallback = (token) => {
                this.captchaToken = token;
                this.fieldsValid.captcha = true;
            };
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
            }),
        });
    },

    _handleBadRequest($dispatch) {
        return (data) => data.violations.forEach((x) => {
            if ('email' === x.property) {
                $dispatch('x-validate:membership_request_email', {
                    status: data.status,
                    message: x.message,
                });
            }

            if ('recaptcha' === x.property) {
                this.generalNotification = {
                    status: data.status,
                    message: x.message,
                };
            }
        });
    },

    async handleOnSubmit(e, $dispatch) {
        this._handleOnSubmitBase(e);
        this.loading = true;
        return this._postPersistEmail()
            .then((response) => {
                if (response.ok) {
                    this.handleNextStep();
                } else {
                    response.json()
                        .then(this._handleBadRequest($dispatch));
                }
            })
            .catch((error) => {
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
