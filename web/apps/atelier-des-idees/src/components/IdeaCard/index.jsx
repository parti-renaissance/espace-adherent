import React from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';

function IdeaCard(props) {
    return (
        <div className="idea-card">
            <div className="idea-card__content">
                <p className="idea-card__content__title">{props.title}</p>
                <div className="idea-card__content__infos">
                    <span className="idea-card__content__infos__author">
                        <span className="idea-card__content__infos__meta">Par</span>
                        <span className="idea-card__content__infos__author__name">{props.author.name}</span>
                        <span className="idea-card__content__infos__author__separator" />
                        <span
                            className={classnames(
                                'idea-card__content__infos__author__type',
                                `idea-card__content__infos__author__type--${props.author.type.id}`
                            )}
                        >
                            {props.author.type.text}
                        </span>
                    </span>
                    <span className="idea-card__content__infos__date">
                        {new Date(props.createdAt).toLocaleDateString()}
                    </span>
                    <span className="idea-card__content__infos__contributors">
                        <img
                            className="idea-card__content__infos__contributors__icon"
                            src="/assets/img/icn_20px_contributors.svg"
                        />
                        <span className="idea-card__content__infos__contributors__text">{props.nbContributors}</span>
                    </span>
                    <span className="idea-card__content__infos__comments">
                        <img
                            className="idea-card__content__infos__contributors__icon"
                            src="/assets/img/icn_20px_comments.svg"
                        />
                        <span className="idea-card__content__infos__contributors__text">{props.nbComments}</span>
                    </span>
                </div>
                <p className="idea-card__content__description">{props.description}</p>
                <ul className="idea-card__content__tags">
                    {props.tags.map(tag => (
                        <li className="idea-card__content__tags__item">{tag}</li>
                    ))}
                </ul>
            </div>
            <div className="idea-card__container">
                <img className="idea-card__container__icon" src={props.thumbnail} />
            </div>
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
        type: PropTypes.shape({
            id: PropTypes.string.isRequired,
            text: PropTypes.string.isRequired,
        }),
    }).isRequired,
    thumbnail: PropTypes.string.isRequired,
    createdAt: PropTypes.string.isRequired, // ISO UTC
    description: PropTypes.string.isRequired,
    nbComments: PropTypes.number,
    nbContributors: PropTypes.number,
    tags: PropTypes.arrayOf(PropTypes.string), // array of ids
    title: PropTypes.string.isRequired,
};

export default IdeaCard;
