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
            {element.dataset.confirmTitle ?
                <div className="text--bold text--default-large">{element.dataset.confirmTitle}</div> : ''
            }
            <p className="b__nudge--top-15 b__nudge--bottom-large text--dark">
                {element.dataset.confirmContent || 'Êtes-vous sûr ?'}
            </p>
            <div>
                <a href={'#'} onClick={event => cancelCallback(event)}
                   className="btn btn--ghosting--blue toggleModal b__nudge--right-small"
                >
                    Annuler
                </a>

                <a href={element.href} className={'btn btn--blue'}>Confirmer</a>
            </div>
        </div>
    );
}

export default function () {
    on(document, 'click', (event) => {
        const element = event.target;
        if (!hasClass(element, 'em-confirm--trigger')) {
            return;
        }
        event.preventDefault();
        const modalWrapper = document.createElement('div');
        modalWrapper.style.display = 'inline-block';
        element.parentNode.insertBefore(modalWrapper, element);
        modal = render(
            <Modal
                contentCallback={() => contentCallback(element)}
                closeCallback={() => { modalWrapper.remove(); }}
            />,
            modalWrapper
        );
    });
}
