import React from 'react';
import { render } from 'react-dom';
import VoteCommitteeWidget from '../components/VoteCommitteeWidget';

let nbClick = 0;

export default (switchSelector, api) => {
    findAll(document, switchSelector).forEach((element) => {
        on(element, 'click', () => {
            nbClick += 1;

            render(
                <VoteCommitteeWidget
                    key={`modal-${nbClick}`}
                    api={api}
                    switcher={element}
                    switchSelector={switchSelector}
                />,
                dom('#modal-wrapper')
            );
        });
    });
};
