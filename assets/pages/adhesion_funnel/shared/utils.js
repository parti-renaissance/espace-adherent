import { captureException } from '@sentry/browser';

export function postAccount(data) {
    return fetch('/api/create-account', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            ...data,
            utm_source: document.querySelector('#membership_request_utmSource').value,
            utm_campaign: document.querySelector('#membership_request_utmCampaign').value,
        }),
    });
}

/**
 * @param {Response} response
 * @param {(payload: any) => void} onSuccess
 * @param {{ error: string }} opt
 * @return {Promise<Response>}
 */
export function handlePostAccountResponse(
    response,
    onSuccess,
    opt = {
        error: 'Une erreur est survenue lors de la création de votre compte',
    }
) {
    return response
        .json()
        .then((payload) => {
            if (this.isNotifResponse(payload)) {
                if ('success' === payload.status) {
                    onSuccess(payload);
                    return;
                }
                if ('redirect' === payload.status) {
                    window.location.href = payload.location;
                    return;
                }
                if (payload.violations) {
                    this._handleBadRequest(payload);
                    this.scrollToFirstError();
                    return;
                }
                this.generalNotification = payload;
                if ('error' === payload.status) {
                    this.scrollToFirstError();
                }
            } else {
                throw new Error('Invalid response');
            }
        })
        .catch((err) => {
            this.generalNotification = {
                status: 'error',
                message: opt.error,
            };
            this.scrollToFirstError();
            captureException(err, {
                tags: {
                    component: 'membership-request',
                    step: 'create-account',
                },
            });
        })
        .finally(() => {
            this.loading = false;
        });
}
