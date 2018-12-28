import React from 'react';
import PropTypes from 'prop-types';

class IdeaReader extends React.PureComponent {
    render() {
        return (
            <div className="idea-reader">
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
    paragraphs: PropTypes.arrayOf(PropTypes.string),
};

export default IdeaReader;
