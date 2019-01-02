import React from 'react';
import PropTypes from 'prop-types';
import TextArea from '../../../components/TextArea';

function IdeaPageTitle(props) {
    return (
        <section className="idea-page-title">
            {props.isEditing ? (
                <TextArea
                    maxLength={120}
                    onChange={props.onTitleChange}
                    placeholder="Titre de l'idée"
                    value={props.title}
                    error={props.hasError ? 'Merci de remplir un titre avant de poursuivre' : undefined}
                />
            ) : (
                <React.Fragment>
                    <h1 className="idea-page-title__title">{props.title}</h1>
                    <div className="idea-page-title__info">
                        {props.authorName && (
                            <span className="idea-page-title__info__author">
                                Par <span className="idea-page-title__info__author-name">{props.authorName}</span>
                            </span>
                        )}
                        {props.createdAt && <span className="idea-page-title__info__date"> le {props.createdAt}</span>}
                    </div>
                </React.Fragment>
            )}
        </section>
    );
}

IdeaPageTitle.defaultProps = {
    authorName: '',
    createdAt: '',
    hasError: false,
    isEditing: false,
};

IdeaPageTitle.propTypes = {
    authorName: PropTypes.string,
    createdAt: PropTypes.string,
    hasError: PropTypes.bool,
    onTitleChange: PropTypes.func.isRequired,
    isEditing: PropTypes.bool,
    title: PropTypes.string.isRequired,
};

export default IdeaPageTitle;
