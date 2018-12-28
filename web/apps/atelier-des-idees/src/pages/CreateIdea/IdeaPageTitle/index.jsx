import React from 'react';
import PropTypes from 'prop-types';
import TextArea from '../../../components/TextArea';

function IdeaPageTitle(props) {
    return (
        <section className="idea-page-title">
            {props.isEditing ? (
                <TextArea
                    maxLength={120}
                    onChange={value => props.onTitleChange('title', value)}
                    placeholder="Titre de l'idée"
                    value={props.title}
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
    isEditing: false,
};

IdeaPageTitle.propTypes = {
    authorName: PropTypes.string,
    createdAt: PropTypes.string,
    isEditing: PropTypes.bool,
    title: PropTypes.string.isRequired,
};

export default IdeaPageTitle;
