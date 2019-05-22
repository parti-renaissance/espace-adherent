import React from 'react';
import { render } from 'react-dom';
import MessageStatisticsWidget from '../components/MessageStatisticsWidget';

export default (buttonClass, api) => {
    findAll(document, `.${buttonClass}`).forEach((element) => {
        on(element, 'click', () => {
            render(<MessageStatisticsWidget api={api} />, dom('#modal-wrapper')).displayModal({
                uuid: element.dataset.uuid,
                subject: element.dataset.subject,
            });
        });
    });
};
