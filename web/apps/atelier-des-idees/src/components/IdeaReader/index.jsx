import React from 'react';
import PropTypes from 'prop-types';

class IdeaReader extends React.PureComponent {
    render() {
        return (
            <div className="idea-reader">
                <h1 className="idea-reader__title">{this.props.title}</h1>
                <div className="idea-reader__info">
                    <span className="idea-reader__info__author">
                        Par <span className="idea-reader__info__author-name">{this.props.authorName}</span>
                    </span>
                    <span className="idea-reader__info__date"> le {this.props.createdAt}</span>
                </div>
                <div className="idea-reader__content">
                    {this.props.paragraphs.map(paragraph => (
                        <div
                            className="idea-reader__content__paragraph"
                            dangerouslySetInnerHTML={{ __html: paragraph }}
                        />
                    ))}
                </div>
            </div>
        );
    }
}

IdeaReader.defaultProps = {
    paragraphs: [],
};

IdeaReader.propTypes = {
    authorName: PropTypes.string.isRequired,
    createdAt: PropTypes.string.isRequired,
    title: PropTypes.string.isRequired,
    paragraphs: PropTypes.arrayOf(PropTypes.string),
};

export default IdeaReader;
