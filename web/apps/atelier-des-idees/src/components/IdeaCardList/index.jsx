import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import ReactTooltip from 'react-tooltip';

import IdeaCardSkeletonList from '../Skeletons/IdeaCardSkeletonList';
import IdeaCard from '../IdeaCard';

const IdeaCardList = (props) => {
    if (props.isLoading) {
        return <IdeaCardSkeletonList nbItems={props.nbSkeletons} mode={props.mode} />;
    }
    return (
        <div
            className={classNames('idea-card-list', {
                'idea-card-list--grid': 'grid' === props.mode,
            })}
        >
            <ReactTooltip />
            {props.ideas.map(idea => (
                <div className="idea-card-list__wrapper" key={idea.uuid}>
                    <IdeaCard
                        {...idea}
                        onVote={(id, vote) => props.onVoteIdea(id, vote)}
                        hasBeenRead={props.readIdeas.includes(idea.uuid)}
                    />
                </div>
            ))}
        </div>
    );
};

IdeaCardList.defaultProps = {
    isLoading: false,
    mode: 'list',
    nbSkeletons: 6,
    readIdeas: [],
};

IdeaCardList.propTypes = {
    ideas: PropTypes.array.isRequired,
    isLoading: PropTypes.bool,
    mode: PropTypes.oneOf(['list', 'grid']),
    nbSkeletons: PropTypes.number,
    onVoteIdea: PropTypes.func,
    readIdeas: PropTypes.arrayOf(PropTypes.string), // array of uuids
};

export default IdeaCardList;
