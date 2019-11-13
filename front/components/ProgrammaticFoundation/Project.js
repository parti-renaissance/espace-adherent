import React, {PropTypes} from 'react';

export default class Project extends React.Component {
    render() {
        const sectionIdentifier = this.props.parentSectionIdentifierParts.concat(this.props.project.position).join('.');

        return (
            <div className="programmatic-foundation__project child">
                <input
                    type="checkbox"
                    id={sectionIdentifier}
                    className="hidden-toggle"
                    defaultChecked={this.props.project.isExpanded && !this.props.preventAutoExpand}
                />

                <label className="head" htmlFor={sectionIdentifier}>
                    <span className="title">{this.props.project.title}</span>
                    <div className="toggle" />
                </label>

                <div className="content">
                    <div className="html" dangerouslySetInnerHTML={{ __html: this.props.project.content }} />
                </div>
            </div>
        );
    }
}

Project.propsType = {
    project: PropTypes.object.isRequired,
    parentSectionIdentifierParts: PropTypes.arrayOf(PropTypes.string).isRequired,
    preventAutoExpand: PropTypes.bool,
};
