import React from 'react';
import { createRoot } from 'react-dom/client';
import Modal from '../components/Modal';

const modalRef = React.createRef();

function cancelCallback(event) {
    event.preventDefault();
    modalRef.current.hideModal();
}

function contentCallback(element) {
    return (
        <div className="font-roboto">
            {element.dataset.confirmTitle
                ? <div className="text--bold text--default-large">{element.dataset.confirmTitle}</div> : ''
            }
            <p className="b__nudge--top-15 b__nudge--bottom-large text--dark">
                {element.dataset.confirmContent || 'Êtes-vous sûr ?'}
            </p>
            <div>
                <a href={'#'} onClick={(event) => cancelCallback(event)}
                    className="btn btn--ghosting--blue toggleModal b__nudge--right-small"
                >
                    Annuler
                </a>

                <a href={element.href} className={'btn btn--blue'}>{element.dataset.confirmAction || 'Confirmer'}</a>
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
        root.render(
            <Modal
                ref={modalRef}
                contentCallback={() => contentCallback(element)}
                closeCallback={() => { root.unmount(); }}
            />
        );
    });
};
