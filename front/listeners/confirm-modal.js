import React from 'react';
import { render } from 'react-dom';
import Modal from '../components/Modal';

let modal;

function cancelCallback(event) {
    event.preventDefault();
    modal.hideModal();
}

function contentCallback(element) {
    return (
        <div className="font-roboto">
            <p className="b__nudge--top-15 b__nudge--bottom-large text--dark">
                {element.dataset.confirmContent || 'Êtes-vous sûr ?'}
            </p>

            <hr/>

            <div>
                <a href={'#'} onClick={event => cancelCallback(event)}
                   className="btn btn--ghosting--blue toggleModal b__nudge--right-nano"
                >
                    Annuler
                </a>

                <a href={element.href} className={'btn btn--blue'}>Confirmer</a>
            </div>
        </div>
    );
}

export default function () {
    findAll(document, '.em-confirm--trigger').forEach((element) => {
        on(element, 'click', (event) => {
            event.preventDefault();

            const modalWrapper = document.createElement('div');
            element.parentNode.insertBefore(modalWrapper, element);

            modal = render(
                <Modal
                    contentCallback={() => contentCallback(element)}
                    closeCallback={() => { modalWrapper.remove(); }}
                />,
                modalWrapper
            );
        });
    });
}
