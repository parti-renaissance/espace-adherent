import React from 'react';
import { connect } from 'react-redux';
import { selectCurrentIdea } from '../../../redux/selectors/currentIdea';
import { voteCurrentIdea } from '../../../redux/thunk/currentIdea';
import VoteButton from '../../../components/VoteButton';

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

function VotingFooterIdeaPage(props) {
    const votes = formatVotes(props.votesCount);
    return (
        <div className="voting-footer-idea-page">
            <h2 className="voting-footer-idea-page__title">Je soutiens cette proposition car elle est&nbsp;: </h2>
            <div className="voting-footer-idea-page__vote">
                {votes.map((vote, i) => (
                    <VoteButton
                        key={i}
                        vote={vote}
                        onSelected={props.onVote}
                        className="voting-footer-idea-page__vote__button"
                    />
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
