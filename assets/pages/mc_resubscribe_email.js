import React from 'react';
import { createRoot } from 'react-dom/client';
import MailchimpResubscribeEmail from '../components/Mailchimp/MailchimpResubscribeEmail';

export default (api, redirectUrl, signupPayload, authenticated, callback, uuid = null, apiKey = null) => createRoot(dom('#modal-wrapper')).render(
    <MailchimpResubscribeEmail
        api={api}
        redirectUrl={redirectUrl}
        signupPayload={signupPayload}
        authenticated={authenticated}
        callback={callback}
        uuid={uuid}
        apiKey={apiKey}
    />
);
