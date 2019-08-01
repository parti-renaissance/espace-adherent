import React from 'react';

class IdeaPageSkeleton extends React.PureComponent {
    render() {
        const skeletonItems = [];
        for (let i = 0; 3 > i; i += 1) {
            skeletonItems.push(
                <div key={`skeleton__${i}`} className="idea-page-skeleton__item">
                    <div className="idea-page-skeleton__item__title" />
                    <div className="idea-page-skeleton__item__body" />
                </div>
            );
        }
        return (
            <div className="idea-page-skeleton">
                <div className="idea-page-skeleton__title" />
                {skeletonItems}
            </div>
        );
    }
}

export default IdeaPageSkeleton;
