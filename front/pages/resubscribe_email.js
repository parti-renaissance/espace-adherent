import React from 'react';
import { render } from 'react-dom';
import ResubscribeEmail from '../components/ResubscribeEmail';

export default (api, redirectUrl, signupPayload, authenticated, callback) => {
    render(
        <ResubscribeEmail
            api={api}
            redirectUrl={redirectUrl}
            signupPayload={signupPayload}
            authenticated={authenticated}
            callback={callback}
        />,
        dom('#modal-wrapper')
    );
};
