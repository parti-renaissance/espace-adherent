import React from 'react';
import PropTypes from 'prop-types';
import Button from '../../../components/Button';

function CreateIdeaActions(props) {
    return (
        <div className="create-idea-actions">
            <div className="create-idea-actions--left">
                <button className="button create-idea-actions__delete" onClick={() => props.onDeleteClicked()}>
                    Supprimer la note
                </button>
            </div>
            {props.isEditing && (
                <div className="create-idea-actions--right">
                    <Button
                        className="create-idea-actions__save"
                        label="Enregistrer le brouillon"
                        mode="secondary"
                        onClick={props.onSaveClicked}
                    />
                    <Button
                        className="create-idea-actions__publish"
                        disabled={props.isEditing && !props.canPublish}
                        label="Publier la note"
                        onClick={props.onPublishClicked}
                    />
                </div>
            )}
        </div>
    );
}

CreateIdeaActions.defaultProps = {
    isEditing: true,
    canPublish: false,
    onBackClicked: undefined,
};

CreateIdeaActions.propTypes = {
    isEditing: PropTypes.bool,
    canPublish: PropTypes.bool,
    onBackClicked: PropTypes.func,
    onDeleteClicked: PropTypes.func.isRequired,
    onPublichClicked: PropTypes.func.isRequired,
    onSaveClicked: PropTypes.func.isRequired,
};

export default CreateIdeaActions;
