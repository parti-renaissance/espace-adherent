import React from 'react';
import PropTypes from 'prop-types';

class IdeaReader extends React.PureComponent {
    render() {
        return (
            <div className="idea-reader">
                <h1 className="idea-reader__title">{this.props.title}</h1>
                <div className="idea-reader__info">
                    {this.props.authorName && (
                        <span className="idea-reader__info__author">
                            Par <span className="idea-reader__info__author-name">{this.props.authorName}</span>
                        </span>
                    )}
                    {this.props.createdAt && (
                        <span className="idea-reader__info__date"> le {this.props.createdAt}</span>
                    )}
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
    authorName: '',
    createdAt: '',
    paragraphs: [],
};

IdeaReader.propTypes = {
    authorName: PropTypes.string,
    createdAt: PropTypes.string,
    title: PropTypes.string.isRequired,
    paragraphs: PropTypes.arrayOf(PropTypes.string),
};

export default IdeaReader;
