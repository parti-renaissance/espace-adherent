import React from 'react';
import PropTypes from 'prop-types';

function IdeaCard(props) {
    return (
        <div className="idea-card">
            <p className="idea-card__title">{props.title}</p>
            <div className="idea-card__infos">
                <span className="idea-card__infos__author">
                    <span className="idea-card__infos__author__type">{props.author.type}</span>
                    <span>Par</span>
                    <span className="idea-card__infos__author__name">{props.author.name}</span>
                </span>
                <span className="idea-card__infos__date">{new Date(props.createdAt).toLocaleDateString()}</span>
                <span className="idea-card__infos__contributors">{props.nbContributors}</span>
                <span className="idea-card__infos__comments">{props.nbComments}</span>
            </div>
            <p className="idea-card__description">{props.description}</p>
            <ul className="idea-card__tags">
                {props.tags.map(tag => (
                    <li className="idea-card__tags__item">{tag}</li>
                ))}
            </ul>
        </div>
    );
}

IdeaCard.defaultProps = {
    nbComments: 0,
    nbContributors: 0,
};

IdeaCard.propTypes = {
    author: PropTypes.shape({
        name: PropTypes.string.isRequired,
        type: PropTypes.string.isRequired,
    }).isRequired,
    createdAt: PropTypes.string.isRequired, // ISO UTC
    description: PropTypes.string.isRequired,
    nbComments: PropTypes.number,
    nbContributors: PropTypes.number,
    tags: PropTypes.arrayOf(PropTypes.string), // array of ids
    title: PropTypes.string.isRequired,
};

export default IdeaCard;
