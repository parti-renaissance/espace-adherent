import React from 'react';
import PropTypes from 'prop-types';
import IdeaCardSkeleton from '../IdeaCardSkeleton';

class IdeaCardSkeletonList extends React.PureComponent {
    render() {
        const skeletons = [];
        for (let i = 0; i < this.props.nbItems; i += 1) {
            skeletons.push(<IdeaCardSkeleton />);
        }
        return <div className="idea-card-skeleton-list">{skeletons}</div>;
    }
}

IdeaCardSkeletonList.defaultProps = {
    nbItems: 1,
};

IdeaCardSkeletonList.propTypes = {
    nbItems: PropTypes.number,
};

export default IdeaCardSkeletonList;
