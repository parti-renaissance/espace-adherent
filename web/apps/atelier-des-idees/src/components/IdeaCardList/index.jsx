import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import IdeaCardSkeletonList from '../Skeletons/IdeaCardSkeletonList';
import IdeaCard from '../IdeaCard';

const IdeaCardList = (props) => {
    if (props.isLoading) {
        return <IdeaCardSkeletonList nbItems={props.nbSkeletons} mode={props.mode} />;
    }
    return (
        <div className={classNames('idea-card-list', { 'idea-card-list--grid': 'grid' === props.mode })}>
            {props.ideas.map(idea => (
                <div className="idea-card-list__wrapper">
                    <IdeaCard {...idea} />
                </div>
            ))}
        </div>
    );
};

IdeaCardList.defaultProps = {
    isLoading: false,
    mode: 'list',
    nbSkeletons: 6,
};

IdeaCardList.propTypes = {
    ideas: PropTypes.array.isRequired,
    isLoading: PropTypes.bool,
    mode: PropTypes.oneOf(['list', 'grid']),
    nbSkeletons: PropTypes.number,
};

export default IdeaCardList;
