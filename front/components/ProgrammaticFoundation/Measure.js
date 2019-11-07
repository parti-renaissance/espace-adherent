import React from 'react';
import Project from './Project';

export default class Measure extends React.Component {
    render() {
        const sectionIdentifier = `${this.props.parentSectionIdentifier}${this.props.measure.position}.`;
        const renderedProjects = this.props.measure.projects.map(
            project => <Project
                         key={project.position}
                         project={project}
                         parentSectionIdentifier={sectionIdentifier}
                       />
        );

        const measureLink = `/socle-programme/mesures/${this.props.measure.slug}`;
        const leadingMesureClass = this.props.measure.isLeading ? 'leading' : null;

        return (
          <div className={`programmatic-foundation__measure child ${leadingMesureClass}`}>
              <input
                type="checkbox"
                id={sectionIdentifier}
                className="hidden-toggle"
                defaultChecked={this.props.measure.isExpanded && !this.props.preventAutoExpand}
              />
              <label className="head" htmlFor={sectionIdentifier}>
                <span className="title">{sectionIdentifier} {this.props.measure.title}</span>
                <div className="toggle" />
              </label>
              <div className="content">
                <div className="measure-links">
                  <a href="#">Copier le lien de la mesure</a>
                  <a href={measureLink} target="_blank">Afficher sur une nouvelle page</a>
                </div>
                <div className="html" dangerouslySetInnerHTML={{ __html: this.props.measure.content }} />
                <div className="programmatic-foundation__children programmatic-foundation__projects">
                  {renderedProjects}
                </div>
              </div>
            </div>
        );
    }
}
