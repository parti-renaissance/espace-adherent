import React from 'react';
import { render } from 'react-dom';
import CandidaciesListWidget from '../components/CandidaciesListWidget';

let nbClick = 0;

export default (triggerSelector, api) => {
    findAll(document, triggerSelector).forEach((element) => {
        on(element, 'click', (event) => {
            event.preventDefault();
            nbClick += 1;

            render(
                <CandidaciesListWidget
                    api={api}
                    committeeUuid={element.dataset.uuid}
                    key={`modal-${nbClick}`}
                />,
                dom('#modal-wrapper')
            );
        });
    });
};
