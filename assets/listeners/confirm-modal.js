import React from 'react';
import { createRoot } from 'react-dom/client';
import Modal from '../components/Modal';

function contentCallback(element, closeCallback) {
    return (
        <div className="font-roboto">
            <div className="font-bold text-xl">{element.dataset.confirmTitle ? element.dataset.confirmTitle : 'Confirmation'}</div>
            <p className="mt-5">{element.dataset.confirmContent || 'Êtes-vous sûr ?'}</p>
            <div className="mt-5 flex justify-between">
                <a href={'#'} onClick={closeCallback} className="px-3 py-2 rounded-md border text-sm leading-5 text-re-blue-800">
                    Annuler
                </a>

                <a href={element.href} className={'px-3 py-2 rounded-md text-sm leading-5 bg-re-blue-50 text-re-blue-800 hover:bg-re-blue-100'}>
                    {element.dataset.confirmAction || 'Confirmer'}
                </a>
            </div>
        </div>
    );
}

export default () => {
    on(document, 'click', (event) => {
        const element = event.target;

        if (!hasClass(element, 'em-confirm--trigger')) {
            return;
        }

        event.preventDefault();

        let modalWrapper = dom('#modal-wrapper');

        if (!modalWrapper) {
            modalWrapper = document.createElement('div');
            element.parentNode.insertBefore(modalWrapper, element);
        }

        const root = createRoot(modalWrapper);
        const closeCallback = () => root.unmount();

        root.render(<Modal contentCallback={() => contentCallback(element, closeCallback)} closeCallback={closeCallback} />);
    });
};
