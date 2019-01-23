import React from 'react';

class IdeaCardSkeleton extends React.PureComponent {
    render() {
        return (
            <div className="idea-card-skeleton">
                <div className="idea-card-skeleton__main-line" />
                <div className="idea-card-skeleton__main-line" />
                <div className="idea-card-skeleton__subline">
                    <div className="idea-card-skeleton__subline__item" />
                    <div className="idea-card-skeleton__subline__item" />
                </div>
                <div className="idea-card-skeleton__circle" />
            </div>
        );
    }
}

export default IdeaCardSkeleton;
