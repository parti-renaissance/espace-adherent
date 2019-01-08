import React from 'react';
import PropTypes from 'prop-types';
import Button from '../../Button';

function DeleteIdeaModal(props) {
    return (
        <div className="delete-idea-modal">
            <img src="/assets/img/icn_state_delete.svg" />
            <h1 className="delete-idea-modal__title">Confirmer la suppression ?</h1>
            <p className="delete-idea-modal__subtitle">Êtes vous certain de vouloir supprimer cette note ?</p>
            <div className="delete-ideal-modal__actions">
                <Button label="Supprimer" mode="secondary" onClick={props.onConfirmDelete} />
                <Button label="Annuler" mode="tertiary" onClick={props.closeModal} />
            </div>
        </div>
    );
}

DeleteIdeaModal.propTypes = {
    closeModal: PropTypes.func.isRequired,
    onConfirmDelete: PropTypes.func.isRequired,
};

export default DeleteIdeaModal;
