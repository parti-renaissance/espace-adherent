import React from 'react';
import { createRoot } from 'react-dom/client';
import ResubscribeEmail from '../components/ResubscribeEmail';

export default (api, redirectUrl, signupPayload, authenticated, callback) => {
    createRoot(dom('#modal-wrapper')).render(
        <ResubscribeEmail
            api={api}
            redirectUrl={redirectUrl}
            signupPayload={signupPayload}
            authenticated={authenticated}
            callback={callback}
        />
    );
};
