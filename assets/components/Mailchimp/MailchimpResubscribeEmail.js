import React, { useEffect, useState } from 'react';
import PropTypes from 'prop-types';
import { captureMessage as sentryCaptureMessage } from '@sentry/browser';
import RequestApiClient from '../../services/api/RequestApiClient';
import successImage from '../../../public/images/icons/icn_success.svg';
import Modal from '../Modal';
import Loader from '../Loader';

const getContent = (status) => {
    if ('error' === status) {
        return <div className={'text-red-600'}>
            Une erreur s’est produite. Veuillez réessayer ultérieurement.
        </div>;
    }

    if ('success' === status) {
        return <div>
            <img className={'modal-content__success'} src={successImage} alt={'success image'}/>
            <p className={'text-lg font-medium text-gray-700'}>Félicitations, vous êtes réabonné(e) aux communications<br/>de Renaissance.</p>
        </div>;
    }

    return <Loader />;
};

const callMailchimp = ({
    api, url, payload, callback,
}) => {
    api.sendResubscribeEmail(url, JSON.parse(window.atob(payload)), callback);
};

const MailchimpResubscribeEmail = ({
    api, redirectUrl, authenticated, signupPayload, callback, uuid = null, apiKey = null,
}) => {
    const [status, setStatus] = useState('loading');
    let count = 0;
    let intervalId;

    useEffect(() => {
        if ('loading' === status) {
            const params = {
                api,
                callback: (response) => {
                    if (null === response || !response.result || 'error' === response.result) {
                        sentryCaptureMessage(
                            'Mailchimp resubscribe Email failed',
                            { level: 'error', debug: true, extra: { response } }
                        );
                    }

                    if (uuid) {
                        api.saveResubscribeStatus(uuid, response, apiKey);
                    }

                    setStatus(response.result && 'success' === response.result ? 'saved' : 'error');
                },
            };

            if (signupPayload) {
                callMailchimp(Object.assign(params, {
                    url: signupPayload.url,
                    payload: signupPayload.payload,
                }));
            } else {
                api.getResubscribeEmailPayload(
                    ({ url, payload }) => callMailchimp(Object.assign(params, { url, payload }))
                );
            }
        } else if ('saved' === status) {
            if (authenticated) {
                intervalId = setInterval(
                    () => {
                        api.getMe((data) => {
                            count += 1;
                            if (!!data.email_subscribed || 5 < count) {
                                clearInterval(intervalId);

                                setStatus(data.email_subscribed ? 'success' : 'error');
                            }
                        });
                    },
                    2000
                );
            }
        } else if ('success' === status) {
            if ('function' === typeof callback) {
                callback(api);
            }
        }
    }, [status]);

    return <Modal
        key={status}
        contentCallback={() => <div className={'text-center font-medium font-maax'}>{getContent(status)}</div>}
        closeCallback={() => {
            if (redirectUrl) {
                document.location.href = redirectUrl;
            } else {
                document.location.reload();
            }
        }}
    />;
};

export default MailchimpResubscribeEmail;

MailchimpResubscribeEmail.propTypes = {
    api: PropTypes.instanceOf(RequestApiClient).isRequired,
    redirectUrl: PropTypes.string,
    authenticated: PropTypes.bool,
    signupPayload: PropTypes.instanceOf(Object),
    callback: PropTypes.func,
};
