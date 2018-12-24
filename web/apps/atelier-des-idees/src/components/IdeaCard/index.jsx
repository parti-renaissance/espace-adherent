import React from 'react';
import { ideaStatus } from '../../constants/api';
import PropTypes from 'prop-types';
import classnames from 'classnames';

import VotingFooter from './VotingFooter';
import ContributingFooter from './ContributingFooter';

const AUTHOR_CATEGORY_NAMES = {
    ADHERENT: 'Adhérent',
    COMMITTEE: 'Comité',
    QG: 'LaREM',
};

const VOTES_NAMES = {
    important: 'Important',
    feasible: 'Réalisable',
    innovative: 'Novateur',
};

function formatVotes(votesCount) {
    return Object.keys(votesCount)
        .filter(key => ['important', 'feasible', 'innovative'].includes(key))
        .map(key => ({
            id: key,
            name: VOTES_NAMES[key],
            count: votesCount[key],
            isSelected: votesCount.my_votes.includes(key),
        }));
}

function IdeaCard(props) {
    return (
        <div className="idea-card">
            <div className="idea-card__main">
                <div className="idea-card__content">
                    <p className="idea-card__content__title">{props.name}</p>
                    <div className="idea-card__content__infos">
                        <span className="idea-card__content__infos__author">
                            <span className="idea-card__content__infos__meta">Par</span>
                            <span className="idea-card__content__infos__author__name">
                                {props.author.name}
                            </span>
                            <span className="idea-card__content__infos__author__separator" />
                            <span
                                className={classnames(
                                    'idea-card__content__infos__author__type',
                                    `idea-card__content__infos__author__type--${
                                        props.author_category
                                    }`
                                )}
                            >
                                {AUTHOR_CATEGORY_NAMES[props.author_category]}
                            </span>
                        </span>
                        {/* <span className="idea-card__content__infos__date">
                            {new Date(props.created_at).toLocaleDateString()}
                                </span>*/}
                        {'QG' !== props.author_category && (
                            <div className="idea-card__content__infos__ideas">
                                <span className="idea-card__content__infos__ideas__contributors">
                                    <img
                                        className="idea-card__content__infos__ideas__ontributors__icon"
                                        src="/assets/img/icn_20px_contributors.svg"
                                    />
                                    <span className="idea-card__content__infos__ideas__contributors__text">
                                        {props.contributors_count}
                                    </span>
                                </span>
                                <span className="idea-card__content__infos__ideas__comments">
                                    <img
                                        className="idea-card__content__infos__ideas__contributors__icon"
                                        src="/assets/img/icn_20px_comments.svg"
                                    />
                                    <span className="idea-card__content__infos__ideas__contributors__text">
                                        {props.comments_count}
                                    </span>
                                </span>
                            </div>
                        )}
                    </div>
                    <p className="idea-card__content__description">{props.description}</p>
                    <ul className="idea-card__content__tags">
                        <li className="idea-card__content__tags__item">
                            {props.category.name}
                        </li>
                        <li className="idea-card__content__tags__item">
                            {props.theme.name}
                        </li>
                    </ul>
                </div>
                <div className="idea-card__container">
                    <img className="idea-card__container__icon" src={props.thumbnail} />
                </div>
            </div>
            {/* FOOTER */}
            {'PENDING' === props.status ? (
            // TODO: implement onSelected -> Vote
                <VotingFooter
                    totalVotes={props.votes_count.total}
                    votes={formatVotes(props.votes_count)}
                    onSelected={vote => props.onVote(vote)}
                />
            ) : (
            // TODO: Link to idea
                <ContributingFooter
                    remainingDays={props.days_before_deadline}
                    link="/atelier-des-idees"
                />
            )}
        </div>
    );
}

IdeaCard.defaultProps = {
    comments_count: 0,
    contributors_count: 0,
    thumbnail: undefined,
};

IdeaCard.propTypes = {
    author: PropTypes.shape({
        first_name: PropTypes.string.isRequired,
        last_name: PropTypes.string.isRequired,
    }).isRequired,
    author_category: PropTypes.string.isRequired,
    thumbnail: PropTypes.string,
    created_at: PropTypes.string.isRequired, // ISO UTC
    description: PropTypes.string.isRequired,
    id: PropTypes.string.isRequired,
    name: PropTypes.string.isRequired,
    votes_count: PropTypes.arrayOf(
        PropTypes.shape({
            important: PropTypes.number.isRequired,
            feasible: PropTypes.number.isRequired,
            innovative: PropTypes.number.isRequired,
            total: PropTypes.number.isRequired,
            my_votes: PropTypes.arrayOf(PropTypes.string),
        })
    ).isRequired,
    comments_count: PropTypes.number,
    contributors_count: PropTypes.number,
    tags: PropTypes.arrayOf(PropTypes.string), // array of ids
    days_before_deadline: PropTypes.number.isRequired,
    status: PropTypes.oneOf(ideaStatus).isRequired,
    onVote: PropTypes.func.isRequired,
};

export default IdeaCard;
