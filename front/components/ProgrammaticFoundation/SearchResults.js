import React, {PropTypes} from 'react';
import Measure from './Measure';
import Project from './Project';

export default class SearchResults extends React.Component {
    render() {
        return <div className="programmatic-foundation__search-results">
            <div className="measures">
                <h2>Mesures</h2>

                <div className="programmatic-foundation__children programmatic-foundation__measures">
                    {this.props.results.measures.map((measure, index) => {
                        return <Measure
                            key={index}
                            measure={measure}
                            preventAutoExpand={true}
                            parentSectionIdentifierParts={measure.parentSectionIdentifierParts}
                        />
                    })}
                </div>
            </div>

            <div className="projects">
                <h2>Projets illustratifs</h2>

                <div className="programmatic-foundation__children programmatic-foundation__projects">
                    {this.props.results.projects.map((project, index) => {
                        return <Project
                            key={index}
                            parentSectionIdentifierParts={project.parentSectionIdentifierParts}
                            project={project}
                            preventAutoExpand={true}
                        />
                    })}
                </div>
            </div>
        </div>;
    }
}

SearchResults.propsType = {
    results: PropTypes.object.isRequired,
};
