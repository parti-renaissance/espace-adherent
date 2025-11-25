import React, { useEffect, useState } from 'react';
import PropTypes from 'prop-types';
import { captureMessage as sentryCaptureMessage } from '@sentry/browser';
import Modal from './Modal';
import Loader from './Loader';
import ReqwestApiClient from '../services/api/ReqwestApiClient';
import successImage from '../../public/images/icons/icn_success.svg';

const getContent = (status) => {
    if ('error' === status) {
        return <div className="text--error">Une erreur s’est produite. Veuillez réessayer ultérieurement.</div>;
    }

    if ('success' === status) {
        return (
            <div>
                <img className="modal-content__success" src={successImage} alt={'success image'} />
                <p>
                    Félicitations, vous êtes réabonné(e) aux communications
                    <br />
                    de La République En Marche.
                </p>
            </div>
        );
    }

    return <Loader wrapperClassName={'space--30-0'} />;
};

const callMailchimp = ({ api, url, payload, callback }) => {
    api.sendResubscribeEmail(url, JSON.parse(window.atob(payload)), callback);
};

const ResubscribeEmail = ({ api, redirectUrl, authenticated, signupPayload, callback }) => {
    const [status, setStatus] = useState('loading');
    let count = 0;
    let intervalId;

    useEffect(() => {
        if ('loading' === status) {
            const params = {
                api,
                callback: (response) => {
                    if (null === response || !response.result || 'error' === response.result) {
                        sentryCaptureMessage('Mailchimp resubscribe Email failed', {
                            level: 'error',
                            debug: true,
                            extra: { response },
                        });
                    }
                    setStatus(response.result && 'success' === response.result ? 'saved' : 'error');
                },
            };

            if (signupPayload) {
                callMailchimp(
                    Object.assign(params, {
                        url: signupPayload.url,
                        payload: signupPayload.payload,
                    })
                );
            } else {
                api.getResubscribeEmailPayload(({ url, payload }) => callMailchimp(Object.assign(params, { url, payload })));
            }
        } else if ('saved' === status) {
            if (authenticated) {
                intervalId = setInterval(() => {
                    api.getMe((data) => {
                        count += 1;
                        if (!!data.email_subscribed || 5 < count) {
                            clearInterval(intervalId);

                            setStatus(data.email_subscribed ? 'success' : 'error');
                        }
                    });
                }, 2000);
            } else {
                setStatus('success');
            }
        } else if ('success' === status) {
            if ('function' === typeof callback) {
                callback(api);
            }
        }
    }, [status]);

    return (
        <Modal
            key={status}
            contentCallback={() => <div className="text--center font-roboto">{getContent(status)}</div>}
            closeCallback={() => {
                if (redirectUrl) {
                    document.location.href = redirectUrl;
                } else {
                    document.location.reload();
                }
            }}
        />
    );
};

export default ResubscribeEmail;

ResubscribeEmail.propTypes = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
    redirectUrl: PropTypes.string,
    authenticated: PropTypes.bool,
    signupPayload: PropTypes.instanceOf(Object),
    callback: PropTypes.func,
};
