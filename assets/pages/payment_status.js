import { captureException } from '@sentry/browser';

async function wait(ms) {
    let timeout = null;
    return new Promise((resolve) => {
        timeout = setTimeout(resolve, ms);
    }).catch((error) => {
        clearTimeout(timeout);
        throw error;
    });
}

/**
 * @param {string} paymentCheckUrl
 * @param {number} retry
 */
async function isBankHasReceivedPayment(paymentCheckUrl, retry = 0) {
    if (100 < retry) {
        throw new Error('Too many retries');
    }
    return fetch(paymentCheckUrl, {
        method: 'GET',
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then((payload) => {
            if (!payload.is_success) {
                return wait(3000).then(() => isBankHasReceivedPayment(paymentCheckUrl, retry + 1));
            }
            if (payload.redirect_uri) {
                window.location.href = payload.redirect_uri;
                return new Promise((resolve) => {
                    resolve(payload);
                });
            }
            throw new Error('No redirect uri found');
        })
        .catch((error) => {
            captureException(error, { extra: { paymentCheckUrl } });
        });
}

/**
 * @param {string} paymentCheckUrl
 */
export default async (paymentCheckUrl) => isBankHasReceivedPayment(paymentCheckUrl);
