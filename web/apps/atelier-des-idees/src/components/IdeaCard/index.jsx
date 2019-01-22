import React from 'react';
import { ideaStatus } from '../../constants/api';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import { Link } from 'react-router-dom';

import VotingFooter from './VotingFooter';
import ContributingFooter from './ContributingFooter';

import icn_20px_contributors from './../../img/icn_20px_contributors.svg';
import icn_20px_comments from './../../img/icn_20px_comments.svg';

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
        .filter(key => Object.keys(VOTES_NAMES).includes(key))
        .map(key => ({
            id: key,
            name: VOTES_NAMES[key],
            count: votesCount[key],
            isSelected: !votesCount.my_votes ? false : Object.keys(votesCount.my_votes).includes(key),
        }));
}

class IdeaCard extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            votesState: false, // keep track of voting footer state changes
        };
        // bindings
        this.cardRef = React.createRef();
        this.toggleOutsideHover = this.toggleOutsideHover.bind(this);
        this.handleHoverOutside = this.handleHoverOutside.bind(this);
    }

    toggleOutsideHover(showVotes) {
        if (showVotes) {
            document.addEventListener('mouseover', this.handleHoverOutside);
        } else {
            document.removeEventListener('mouseover', this.handleHoverOutside);
        }
    }

    handleHoverOutside(event) {
        if (this.cardRef) {
            // check if the postion of the mouse is outside of the card
            const isOutofCard = !this.cardRef.current.contains(event.target);
            if (isOutofCard) {
                // change state.votesState to force render the VotingFooter
                this.setState(prevState => ({ votesState: !prevState.votesState }));
                document.removeEventListener('mouseover', this.handleHoverOutside);
            }
        }
    }

    truncate(string, limitation) {
        if (limitation < string.length) {
            return string.substring(0, limitation).concat(' (...)');
        }
        return string;
    }

    render() {
        return (
            <div className="idea-card" ref={this.cardRef}>
                <Link to={`/atelier-des-idees/proposition/${this.props.uuid}`} className="idea-card__link">
                    <div className="idea-card__main">
                        <div className="idea-card__content">
                            <p className="idea-card__content__title">{this.truncate(this.props.name, 53)}</p>
                            <div className="idea-card__content__infos">
                                <span className="idea-card__content__infos__author">
                                    <span className="idea-card__content__infos__meta">Par </span>
                                    <span className="idea-card__content__infos__author__name">
                                        {this.props.author.first_name} {this.props.author.last_name}
                                    </span>
                                    <span className="idea-card__content__infos__author__separator" />
                                    <span
                                        className={classnames(
                                            'idea-card__content__infos__author__type',
                                            `idea-card__content__infos__author__type--${this.props.author_category}`
                                        )}
                                    >
                                        {AUTHOR_CATEGORY_NAMES[this.props.author_category]}
                                    </span>
                                </span>
                                {/* <span className="idea-card__content__infos__date">
                            {new Date(this.props.created_at).toLocaleDateString()}
                                </span>*/}
                                {'QG' !== this.props.author_category && (
                                    <div className="idea-card__content__infos__ideas">
                                        <span className="idea-card__content__infos__ideas__contributors">
                                            <img
                                                className="idea-card__content__infos__ideas__contributors__icon"
                                                src={icn_20px_contributors}
                                            />
                                            <span className="idea-card__content__infos__ideas__contributors__text">
                                                {this.props.contributors_count}
                                            </span>
                                        </span>
                                        <span className="idea-card__content__infos__ideas__comments">
                                            <img
                                                className="idea-card__content__infos__ideas__comments__icon"
                                                src={icn_20px_comments}
                                            />
                                            <span className="idea-card__content__infos__ideas__comments__text">
                                                {this.props.comments_count}
                                            </span>
                                        </span>
                                    </div>
                                )}
                            </div>
                            <p className="idea-card__content__description">{this.props.description}</p>
                            {!!this.props.themes.length && (
                                <ul className="idea-card__content__tags">
                                    {this.props.themes.map((theme, index) => (
                                        <li key={`theme__${index}`} className="idea-card__content__tags__item">
                                            {theme.name}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                        {!!this.props.themes.length && this.props.themes[0].thumbnail && (
                            <div className="idea-card__theme">
                                <img className="idea-card__theme__icon" src={this.props.themes[0].thumbnail} />
                            </div>
                        )}
                    </div>
                </Link>
                {/* FOOTER */}
                {'FINALIZED' === this.props.status ? (
                    <VotingFooter
                        key={`voting-footer__${this.state.votesState}`}
                        totalVotes={this.props.votes_count.total}
                        votes={formatVotes(this.props.votes_count)}
                        onSelected={vote => this.props.onVote(this.props.uuid, vote)}
                        onToggleVotePanel={this.toggleOutsideHover}
                    />
                ) : (
                    <ContributingFooter
                        remainingDays={this.props.days_before_deadline}
                        link={`/atelier-des-idees/proposition/${this.props.uuid}`}
                    />
                )}
            </div>
        );
    }
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
    uuid: PropTypes.string.isRequired,
    name: PropTypes.string.isRequired,
    votes_count: PropTypes.shape({
        important: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
        feasible: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
        innovative: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
        total: PropTypes.number.isRequired,
        my_votes: PropTypes.object,
    }).isRequired,
    comments_count: PropTypes.number,
    contributors_count: PropTypes.number,
    themes: PropTypes.arrayOf(PropTypes.shape({ name: PropTypes.string, thumbnail: PropTypes.string })),
    days_before_deadline: PropTypes.number.isRequired,
    status: PropTypes.oneOf(Object.keys(ideaStatus)).isRequired,
    onVote: PropTypes.func,
};

export default IdeaCard;
