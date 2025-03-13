import React from 'react';
import { createRoot } from 'react-dom/client';
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

            const root = createRoot(modalWrapper);
            root.render(
                <Modal
                    side={element.dataset.modalSide || null}
                    content={dom(element.dataset.contentElement).innerHTML}
                    closeCallback={() => root.unmount()}
                />
            );
        });
    });
};
