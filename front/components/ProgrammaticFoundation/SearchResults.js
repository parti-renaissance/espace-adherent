import React from 'react';
import Measure from './Measure';
import Project from './Project';

export default class SearchResults extends React.Component {
    render() {
        const renderedMeasures = this.props.results.measures.map(
            measure => <Measure
                         key={`${measure.parentSectionIdentifier}.${measure.position}`}
                         parentSectionIdentifier={measure.parentSectionIdentifier}
                         measure={measure}
                         preventAutoExpand={true}
                       />
        );

        const renderedProjects = this.props.results.projects.map(
            project => <Project
                         key={`${project.parentSectionIdentifier}.${project.position}`}
                         parentSectionIdentifier={project.parentSectionIdentifier}
                         project={project}
                         preventAutoExpand={true}
                       />
        );

        return (
            <div className="programmatic-foundation__search-results">
              <div className="measures">
                <h2>Mesures</h2>
                <div className="programmatic-foundation__children programmatic-foundation__measures">
                  {renderedMeasures}
                </div>
              </div>
              <div className="projects">
                <h2>Projets illustratifs</h2>
                <div className="programmatic-foundation__children programmatic-foundation__projects">
                  {renderedProjects}
                </div>
              </div>
            </div>
        );
    }
}
