import React from 'react';
import { render } from 'react-dom';
import CandidaciesListWidget from '../components/CandidaciesListWidget';

let nbClick = 0;

export default (triggerSelector, api, committeeUuid) => {
    on(dom(triggerSelector), 'click', (event) => {
        event.preventDefault();
        nbClick += 1;

        render(
            <CandidaciesListWidget
                api={api}
                committeeUuid={committeeUuid}
                key={`modal-${nbClick}`}
            />,
            dom('#modal-wrapper')
        );
    });
};
