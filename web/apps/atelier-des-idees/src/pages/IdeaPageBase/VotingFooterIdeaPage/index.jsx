import React, { Component } from 'react';
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

class VoteButton extends Component {
    render() {
        const { vote, onVote } = this.props;
        return (
            <button
                key={vote.id}
                className={classNames('button', 'voting-footer-idea-page__vote__button', {
                    'voting-footer-idea-page__vote__button--selected': vote.isSelected,
                }, this.state.animate)}
                onClick={() => {
                    onVote(vote.id);
                }}
            >
                <span className="voting-footer-idea-page__vote__button__name">{vote.name}</span>
                <span className="voting-footer-idea-page__vote__button__count">{vote.count}</span>
            </button>
        );
    }
}

function VotingFooterIdeaPage(props) {
    const votes = formatVotes(props.votesCount);
    return (
        <div className="voting-footer-idea-page">
            <h2 className="voting-footer-idea-page__title">Je soutiens cette proposition car elle est : </h2>
            <div className="voting-footer-idea-page__vote">
                {votes.map(vote => <VoteButton vote={vote} onVote={props.onVote} />)}
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
