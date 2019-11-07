import React from 'react';
import Measure from './Measure';

export default class SubApproach extends React.Component {
    render() {
        const sectionIdentifier = `${this.props.parentSectionIdentifier}${this.props.subApproach.position}.`;
        const renderedMeasures = this.props.subApproach.measures.map(
            measure => <Measure
                         key={measure.position}
                         parentSectionIdentifier={sectionIdentifier}
                         measure={measure}
                       />
        );

        return (
            <div className="programmatic-foundation__sub-approach child">
              <input
                type="checkbox"
                id={sectionIdentifier}
                className="hidden-toggle"
                defaultChecked={this.props.subApproach.isExpanded && !this.props.preventAutoExpand}
              />
              <label className="head" htmlFor={sectionIdentifier}>
                <span className="title">{sectionIdentifier} {this.props.subApproach.title}</span>
                <span className="subtitle">{this.props.subApproach.subtitle}</span>
                <div className="toggle" />
              </label>
              <div className="content">
                <div className="html" dangerouslySetInnerHTML={{ __html: this.props.subApproach.content }} />
                <div className="programmatic-foundation__children programmatic-foundation__measures">
                  {renderedMeasures}
                </div>
              </div>
            </div>
        );
    }
}
