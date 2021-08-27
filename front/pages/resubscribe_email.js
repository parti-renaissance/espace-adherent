import React from 'react';
import { render } from 'react-dom';
import ResubscribeEmail from '../components/ResubscribeEmail';

export default (api) => {
    render(
        <ResubscribeEmail api={api} />,
        dom('#modal-wrapper')
    );
};
