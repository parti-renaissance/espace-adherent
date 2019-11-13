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
                  <a href="#">
                      <svg className="icn" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                        <path fill="#7B889B" d="M13.5,1.5 L13.5,12.5 L10.5,12.5 L10.5,15.5 L2.5,15.5 L2.5,4.5 L5.5,4.5 L5.5,1.5 L13.5,1.5 Z M5.5,5.5 L3.5,5.5 L3.5,14.5 L9.5,14.5 L9.5,12.5 L5.5,12.5 L5.5,5.5 Z M12.5,2.5 L6.5,2.5 L6.5,11.5 L12.5,11.5 L12.5,2.5 Z"/>
                      </svg>
                      Copier le lien de la mesure
                  </a>
                  <a href={measureLink} target="_blank">
                      <svg className="icn" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                        <path fill="#7B889B" d="M5.5632955,2.44999999 L5.5632955,3.55000001 L2.55,3.55 L2.55,13.449 L12.449,13.449 L12.45,10.6170839 L13.55,10.6170839 L13.55,14.55 L1.44999999,14.55 L1.44999999,2.44999999 L5.5632955,2.44999999 Z M13.55,2.44999999 L13.55,7.55000001 L12.45,7.55000001 L12.449,4.327 L8,8.77781748 L7.22218252,8 L11.672,3.55 L8.44999999,3.55000001 L8.44999999,2.44999999 L13.55,2.44999999 Z"/>
                      </svg>
                      Afficher sur une nouvelle page
                  </a>
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
