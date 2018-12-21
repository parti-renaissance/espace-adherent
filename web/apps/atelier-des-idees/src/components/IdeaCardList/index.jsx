import React from 'react';
import PropTypes from 'prop-types';
import IdeaCardSkeletonList from '../Skeletons/IdeaCardSkeletonList';
import IdeaCard from '../IdeaCard';

const IdeaCardList = (props) => {
    if (props.isLoading) {
        return <IdeaCardSkeletonList nbItems={5} />;
    }
    return (
        <div className="idea-card-list">
            {props.ideas.map(idea => (
                <IdeaCard {...idea} />
            ))}
        </div>
    );
};

IdeaCardList.defaultProps = {
    isLoading: false,
};

IdeaCardList.propTypes = {
    ideas: PropTypes.array.isRequired,
    isLoading: PropTypes.bool,
};

export default IdeaCardList;
