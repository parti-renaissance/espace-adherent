import React, { useEffect, useState } from 'react';
import PropTypes from 'prop-types';
import Modal from './Modal';
import Loader from './Loader';
import ReqwestApiClient from '../services/api/ReqwestApiClient';
import successImage from '../../public/images/icons/icn_success.svg';

const getContent = (status) => {
    if ('error' === status) {
        return <div className="text--error">
            Une erreur s’est produite. Veuillez réessayer ultérieurement.
        </div>;
    }

    if ('success' === status) {
        return <div>
            <img className="modal-content__success" src={successImage} alt={'success image'}/>
            <p>Vous êtes réabonné(e)</p>
        </div>;
    }

    return <Loader wrapperClassName={'space--30-0'} />;
};
const ResubscribeEmail = ({ api }) => {
    const [status, setStatus] = useState('loading');
    let count = 0;
    let intervalId;

    useEffect(() => {
        if ('loading' === status) {
            api.getResubscribeEmailPayload(({ url, payload }) => {
                api.sendResubscribeEmail(
                    url,
                    JSON.parse(window.atob(payload)),
                    (response) => setStatus(response.result && 'success' === response.result ? 'saved' : 'error')
                );
            });
        } else if ('saved' === status) {
            intervalId = setInterval(
                () => {
                    api.getMe((data) => {
                        count += 1;
                        if (false === data.email_unsubscribed || 5 < count) {
                            clearInterval(intervalId);

                            setStatus(false === data.email_unsubscribed ? 'success' : 'error');
                        }
                    });
                },
                2000
            );
        }
    }, [status]);

    return <Modal
        key={status}
        contentCallback={() => <div className="text--center font-roboto">{getContent(status)}</div>}
        closeCallback={() => document.location.reload()}
    />;
};

export default ResubscribeEmail;

ResubscribeEmail.propTypes = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
};
