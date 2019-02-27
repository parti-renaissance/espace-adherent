import React from 'react';
import PropTypes from 'prop-types';
import Button from '../../../components/Button';
import { ideaStatus } from '../../../constants/api';

function CreateIdeaActions(props) {
    return (
        <div className="idea-page-actions">
            {props.status === ideaStatus.DRAFT && (
                <div className="idea-page-actions--left">
                    <button className="button idea-page-actions__delete" onClick={() => props.onDeleteClicked()}>
						Supprimer la proposition
                    </button>
                </div>
            )}

            {props.status === ideaStatus.DRAFT && (
                <div className="idea-page-actions--right">
                    <React.Fragment>
                        {props.status === ideaStatus.DRAFT && (
                            <Button
                                className="idea-page-actions__save"
                                label="Enregistrer le brouillon"
                                mode="secondary"
                                onClick={props.onSaveClicked}
                                isLoading={props.isSaving}
                            />
                        )}

                        <Button
                            className="idea-page-actions__publish"
                            label={
                                props.status === ideaStatus.DRAFT
                                    ? 'Publier la proposition'
                                    : 'Modifier les informations'
                            }
                            onClick={props.onPublishClicked}
                        />
                    </React.Fragment>
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
