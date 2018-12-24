import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import IdeaCardSkeleton from '../IdeaCardSkeleton';

class IdeaCardSkeletonList extends React.PureComponent {
    render() {
        const skeletons = [];
        for (let i = 0; i < this.props.nbItems; i += 1) {
            skeletons.push(
                <div className="idea-card-skeleton-list__item-wrapper">
                    <IdeaCardSkeleton />
                </div>
            );
        }
        return (
            <div className={classNames('idea-card-skeleton-list', `idea-card-skeleton-list--${this.props.mode}`)}>
                {skeletons}
            </div>
        );
    }
}

IdeaCardSkeletonList.defaultProps = {
    mode: 'list',
    nbItems: 1,
};

IdeaCardSkeletonList.propTypes = {
    mode: PropTypes.oneOf(['list', 'grid']),
    nbItems: PropTypes.number,
};

export default IdeaCardSkeletonList;
