import React, {PropTypes} from 'react';

export default class Project extends React.Component {
    render() {
        return (
            <div className={`programmatic-foundation__project child ${
                this.props.project.isExpanded && !this.props.preventAutoExpand ? 'expanded' : ''
            }`}>
                <div className="head" onClick={event => toggleClass(event.currentTarget.parentNode, 'expanded')}>
                    <span className="title">{this.props.project.title}</span>
                    <span className="toggle" />
                </div>

                <div className="content">
                    <div className="html" dangerouslySetInnerHTML={{ __html: this.props.project.content }} />
                </div>
            </div>
        );
    }
}

Project.propTypes = {
    project: PropTypes.object.isRequired,
    preventAutoExpand: PropTypes.bool,
};
