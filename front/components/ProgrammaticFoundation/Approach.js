import React from 'react';
import SubApproach from './SubApproach';

export default class Approach extends React.Component {
    render() {
        const sectionIdentifier = `${this.props.approach.position}.`;
        const renderedSubApproaches = this.props.approach.sub_approaches.map(
            subApproach => <SubApproach
                             key={subApproach.position}
                             parentSectionIdentifier={sectionIdentifier}
                             subApproach={subApproach}
                           />
        );

        return (
            <div className="programmatic-foundation__approach">
              <h2>{sectionIdentifier} {this.props.approach.title}</h2>
              <div className="content" dangerouslySetInnerHTML={{ __html: this.props.approach.content }} />
              <div className="programmatic-foundation__sub-approaches programmatic-foundation__children">
                {renderedSubApproaches}
              </div>
            </div>
        );
    }
}
