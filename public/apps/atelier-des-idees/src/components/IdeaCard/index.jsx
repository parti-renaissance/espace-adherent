import React from 'react';
import { ideaStatus } from '../../constants/api';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import { Link } from 'react-router-dom';

import VotingFooter from './VotingFooter';
import ContributingFooter from './ContributingFooter';
import { getUserDisplayName } from '../../helpers/entities';
import { AUTHOR_CATEGORIES } from '../../constants/api';

import icn_20px_contributors from './../../img/icn_20px_contributors.svg';
import icn_20px_comments from './../../img/icn_20px_comments.svg';

const VOTES_NAMES = {
    important: 'Essentielle',
    feasible: 'Réalisable',
    innovative: 'Innovante',
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

    render() {
        return (
            <div
                className={classnames('idea-card', {
                    'idea-card--condensed': this.props.condensed,
                })}
                ref={this.cardRef}
            >
                <Link to={`/atelier-des-idees/proposition/${this.props.uuid}`} className="idea-card__link">
                    <div className="idea-card__main">
                        <div className="idea-card__content">
                            <p
                                className={classnames('idea-card__content__title', {
                                    'idea-card__content__title--read': this.props.hasBeenRead,
                                })}
                                title={this.props.name}
                            >
                                {this.props.name}
                            </p>
                            <div className="idea-card__content__infos">
                                <span className="idea-card__content__infos__author">
                                    <span className="idea-card__content__infos__meta">Par </span>
                                    <span className="idea-card__content__infos__author__name">
                                        {getUserDisplayName(this.props.author)}
                                    </span>
                                    <span className="idea-card__content__infos__author__separator" />
                                    <span
                                        className={classnames(
                                            'idea-card__content__infos__author__type',
                                            `idea-card__content__infos__author__type--${this.props.author_category}`
                                        )}
                                    >
                                        {AUTHOR_CATEGORIES[this.props.author_category]}
                                    </span>
                                </span>
                                {'QG' !== this.props.author_category && (
                                    <div className="idea-card__content__infos__ideas">
                                        {0 < this.props.contributors_count && (
                                            <span
                                                className="idea-card__content__infos__ideas__contributors"
                                                data-tip={`${this.props.contributors_count} contributeurs`}
                                                data-effect="solid"
                                                data-type="light"
                                                data-class="idea-card__tip"
                                                data-place="bottom"
                                            >
                                                <img
                                                    className="idea-card__content__infos__ideas__contributors__icon"
                                                    src={icn_20px_contributors}
                                                />
                                                <span className="idea-card__content__infos__ideas__contributors__text">
                                                    {this.props.contributors_count}
                                                </span>
                                            </span>
                                        )}
                                        {0 < this.props.comments_count && (
                                            <span
                                                className="idea-card__content__infos__ideas__comments"
                                                data-tip={`${this.props.comments_count} commentaires`}
                                                data-effect="solid"
                                                data-type="light"
                                                data-class="idea-card__tip"
                                                data-place="bottom"
                                            >
                                                <img
                                                    className="idea-card__content__infos__ideas__comments__icon"
                                                    src={icn_20px_comments}
                                                />
                                                <span className="idea-card__content__infos__ideas__comments__text">
                                                    {this.props.comments_count}
                                                </span>
                                            </span>
                                        )}
                                    </div>
                                )}
                            </div>
                            {!this.props.condensed && (
                                <p className="idea-card__content__description">{this.props.description}</p>
                            )}
                        </div>
                        {!this.props.condensed && !!this.props.themes.length && (
                            <ul className="idea-card__content__tags">
                                {this.props.themes.map((theme, index) => (
                                    <li key={`theme__${index}`} className="idea-card__content__tags__item">
                                        {theme.name}
                                    </li>
                                ))}
                            </ul>
                        )}
                        {!!this.props.themes.length && this.props.themes[0].thumbnail && (
                            <div className="idea-card__theme">
                                <img
                                    className="idea-card__theme__icon"
                                    src={this.props.themes[0].thumbnail}
                                    data-tip={this.props.themes[0].name}
                                    data-effect="solid"
                                    data-type="light"
                                    data-class="idea-card__theme-tip"
                                />
                            </div>
                        )}
                    </div>
                </Link>
                {/* FOOTER */}
                {'FINALIZED' === this.props.status ? (
                    <VotingFooter
                        condensed={this.props.condensed}
                        key={`voting-footer__${this.state.votesState}`}
                        totalVotes={this.props.votes_count.total}
                        votes={formatVotes(this.props.votes_count)}
                        onSelected={vote => this.props.onVote(this.props.uuid, vote)}
                        onToggleVotePanel={this.toggleOutsideHover}
                        hasUserVoted={
                            this.props.votes_count.my_votes && !!Object.keys(this.props.votes_count.my_votes).length
                        }
                    />
                ) : (
                    <ContributingFooter
                        condensed={this.props.condensed}
                        remainingDays={this.props.days_before_deadline}
                        remainingHours={this.props.hours_before_deadline}
                        link={`/atelier-des-idees/proposition/${this.props.uuid}`}
                        hasUserContributed={this.props.contributed_by_me}
                    />
                )}
            </div>
        );
    }
}

IdeaCard.defaultProps = {
    comments_count: 0,
    condensed: false,
    contributed_by_me: false,
    contributors_count: 0,
    thumbnail: undefined,
    hasBeenRead: false,
};

IdeaCard.propTypes = {
    author: PropTypes.shape({
        first_name: PropTypes.string,
        last_name: PropTypes.string,
        nickname: PropTypes.string,
    }).isRequired,
    author_category: PropTypes.string.isRequired,
    comments_count: PropTypes.number,
    condensed: PropTypes.bool,
    contributed_by_me: PropTypes.bool,
    contributors_count: PropTypes.number,
    created_at: PropTypes.string.isRequired, // ISO UTC
    days_before_deadline: PropTypes.number.isRequired,
    description: PropTypes.string, // this is null sometimes
    hasBeenRead: PropTypes.bool,
    hours_before_deadline: PropTypes.number.isRequired,
    name: PropTypes.string.isRequired,
    onVote: PropTypes.func,
    status: PropTypes.oneOf(Object.keys(ideaStatus)).isRequired,
    themes: PropTypes.arrayOf(PropTypes.shape({ name: PropTypes.string, thumbnail: PropTypes.string })),
    thumbnail: PropTypes.string,
    uuid: PropTypes.string.isRequired,
    votes_count: PropTypes.shape({
        important: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
        feasible: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
        innovative: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
        total: PropTypes.number.isRequired,
        my_votes: PropTypes.object,
    }).isRequired,
};

export default IdeaCard;
