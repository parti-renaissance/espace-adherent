import React from 'react';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { selectCurrentIdea } from '../../../redux/selectors/currentIdea';
import { voteCurrentIdea } from '../../../redux/thunk/currentIdea';

const VOTES_NAMES = {
    important: 'Essentiel',
    feasible: 'Réalisable',
    innovative: 'Innovant',
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

function VotingFooterIdeaPage(props) {
    const votes = formatVotes(props.votesCount);
    return (
        <div className="voting-footer-idea-page">
            <h2 className="voting-footer-idea-page__title">Votez pour cette proposition !</h2>
            <div className="voting-footer-idea-page__vote">
                {votes.map(vote => (
                    <button
                        key={vote.id}
                        className={classNames('button', 'voting-footer-idea-page__vote__button', {
                            'voting-footer-idea-page__vote__button--selected': vote.isSelected,
                        })}
                        onClick={() => {
                            props.onVote(vote.id);
                        }}
                    >
                        <span className="voting-footer-idea-page__vote__button__name">{vote.name}</span>
                        <span className="voting-footer-idea-page__vote__button__count">{vote.count}</span>
                    </button>
                ))}
            </div>
        </div>
    );
}

function mapStateToProps(state) {
    const currentIdea = selectCurrentIdea(state);
    return { votesCount: currentIdea.votes_count };
}

export default connect(
    mapStateToProps,
    { onVote: typeVote => voteCurrentIdea(typeVote) }
)(VotingFooterIdeaPage);
