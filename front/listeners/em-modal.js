import React from 'react';
import { render, unmountComponentAtNode } from 'react-dom';
import Modal from '../components/Modal';

export default () => {
    findAll(document, '.em-modal--trigger').forEach((element) => {
        on(element, 'click', (event) => {
            event.preventDefault();

            let modalWrapper = dom('#modal-wrapper');

            if (!modalWrapper) {
                modalWrapper = document.createElement('div');
                element.parentNode.insertBefore(modalWrapper, element);
            }

            render(
                <Modal
                    side={element.dataset.modalSide || null}
                    content={dom(element.dataset.contentElement).innerHTML}
                    closeCallback={() => unmountComponentAtNode(modalWrapper)}
                />,
                modalWrapper
            );
        });
    });
};
