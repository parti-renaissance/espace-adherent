import React from 'react';
import { render } from 'react-dom';
import AdherentCommitteeList from '../components/AdherentCommitteeList';

export default (buttonClass, api) => {
    findAll(document, `.${buttonClass}`).forEach((element) => {
        on(element, 'click', () => {
            render(<AdherentCommitteeList api={api} />, dom('#modal-wrapper')).displayModal({
                uuid: element.dataset.uuid,
                adherent_name: element.getAttribute('data-adherent-name'),
            });
        });
    });
};
