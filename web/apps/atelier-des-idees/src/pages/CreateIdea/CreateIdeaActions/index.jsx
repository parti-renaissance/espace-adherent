import React from 'react';
import PropTypes from 'prop-types';
import Button from '../../../components/Button';

function CreateIdeaActions(props) {
    return (
        <div className="create-idea-actions">
            <div className="create-idea-actions--left">
                {'header' === props.mode && (
                    <React.Fragment>
                        <button className="button create-idea-actions__back" onClick={() => props.onBackClicked()}>
                            Retour
                        </button>
                    </React.Fragment>
                )}
                <button className="button create-idea-actions__delete" onClick={() => props.onDeleteClicked()}>
                    Supprimer la note
                </button>
            </div>
            <div className="create-idea-actions--left">
                <Button
                    className="create-idea-actions__save"
                    label="Enregistrer le brouillon"
                    mode="secondary"
                    onClick={props.onSaveClicked}
                />
                <Button
                    className="create-idea-actions__publish"
                    label="Publier la note"
                    onClick={props.onPublishClicked}
                />
            </div>
        </div>
    );
}

CreateIdeaActions.defaultProps = {
    mode: 'header',
    onBackClicked: undefined,
};

CreateIdeaActions.propTypes = {
    mode: PropTypes.oneOf(['header', 'footer']),
    onBackClicked: PropTypes.func,
    onDeleteClicked: PropTypes.func.isRequired,
    onPublichClicked: PropTypes.func.isRequired,
    onSaveClicked: PropTypes.func.isRequired,
};

export default CreateIdeaActions;
