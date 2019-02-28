import React from 'react';
import PropTypes from 'prop-types';
import Button from '../../../components/Button';

function CreateIdeaActions(props) {
    return (
        <div className="create-idea-actions">
            <div className="create-idea-actions--left">
                <button className="button create-idea-actions__delete" onClick={() => props.onDeleteClicked()}>
                    Supprimer la proposition
                </button>
            </div>
            {(props.isDraft || props.canPublish) && (
                <div className="create-idea-actions--right">
                    {props.isDraft && (
                        <Button
                            className="create-idea-actions__save"
                            label="Enregistrer le brouillon"
                            mode="secondary"
                            onClick={props.onSaveClicked}
                            isLoading={props.isSaving}
                        />
                    )}
                    {props.canPublish && (
                        <Button
                            className="create-idea-actions__publish"
                            label={props.isDraft ? 'Publier la proposition' : 'Modifier les informations'}
                            onClick={props.onPublishClicked}
                        />
                    )}
                </div>
            )}
        </div>
    );
}

CreateIdeaActions.defaultProps = {
    isDraft: false,
    isSaving: false,
    canPublish: false,
    onBackClicked: undefined,
};

CreateIdeaActions.propTypes = {
    canPublish: PropTypes.bool,
    isDraft: PropTypes.bool,
    isSaving: PropTypes.bool,
    onBackClicked: PropTypes.func,
    onDeleteClicked: PropTypes.func.isRequired,
    onPublishClicked: PropTypes.func.isRequired,
    onSaveClicked: PropTypes.func.isRequired,
};

export default CreateIdeaActions;
