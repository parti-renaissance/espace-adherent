import React from 'react';
import PropTypes from 'prop-types';
import IdeaCardSkeletonList from '../Skeletons/IdeaCardSkeletonList';

const IdeaCardList = (props) => {
    if (props.isLoading) {
        return <IdeaCardSkeletonList nbItems={5} />;
    }
    return (
        <div className="idea-card-list">
            {/* TODO: use IdeaCard */}
            {props.ideas.map(idea => (
                <div>{idea.title}</div>
            ))}
        </div>
    );
};

IdeaCardList.defaultProps = {
    isLoading: false,
};

IdeaCardList.propTypes = {
    ideas: PropTypes.arrayOf(
        PropTypes.shape({
            author: PropTypes.string.isRequired,
            author_category: PropTypes.string.isRequired,
            date: PropTypes.string.isRequired,
            description: PropTypes.string.isRequired,
            id: PropTypes.string.isRequired,
            status: PropTypes.string.isRequired,
            theme: PropTypes.array.isRequired,
            thumbnail: PropTypes.string.isRequired,
            title: PropTypes.string.isRequired,
        })
    ),
    isLoading: PropTypes.bool,
};

export default IdeaCardList;
