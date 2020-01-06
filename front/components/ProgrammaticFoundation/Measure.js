import React, {PropTypes} from 'react';
import Project from './Project';
import ReactDOM from 'react-dom';

export default class Measure extends React.Component {
    render() {
        return (
            <div className={`programmatic-foundation__measure child
            ${this.props.measure.isExpanded && !this.props.preventAutoExpand ? 'expanded' : ''}`}>

                <div className="head" onClick={this.toggleActiveMeasure.bind(this)}>
                    <span className="title">{this.props.measure.title}</span>
                    <span className="toggle" />
                </div>

                <div className="content">
                    <div className="html" dangerouslySetInnerHTML={{ __html: this.props.measure.content }} />

                    { !!this.props.measure.projects.length && this.renderProjects() }

                    <div className="measure-links">
                        <a href="#" onClick={this.handleCopyAction.bind(this)} data-success-title="Copié">
                            <svg className="icn" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                <path fill="#7B889B" d="M13.5,1.5 L13.5,12.5 L10.5,12.5 L10.5,15.5 L2.5,15.5 L2.5,4.5 L5.5,4.5 L5.5,1.5 L13.5,1.5 Z M5.5,5.5 L3.5,5.5 L3.5,14.5 L9.5,14.5 L9.5,12.5 L5.5,12.5 L5.5,5.5 Z M12.5,2.5 L6.5,2.5 L6.5,11.5 L12.5,11.5 L12.5,2.5 Z"/>
                            </svg>
                            Copier le lien de la mesure
                        </a>
                        <a href={this.getMeasureUrl()} target="_blank">
                            <svg className="icn" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                <path fill="#7B889B" d="M5.5632955,2.44999999 L5.5632955,3.55000001 L2.55,3.55 L2.55,13.449 L12.449,13.449 L12.45,10.6170839 L13.55,10.6170839 L13.55,14.55 L1.44999999,14.55 L1.44999999,2.44999999 L5.5632955,2.44999999 Z M13.55,2.44999999 L13.55,7.55000001 L12.45,7.55000001 L12.449,4.327 L8,8.77781748 L7.22218252,8 L11.672,3.55 L8.44999999,3.55000001 L8.44999999,2.44999999 L13.55,2.44999999 Z"/>
                            </svg>
                            Afficher sur une nouvelle page
                        </a>
                    </div>

                </div>
            </div>
        );
    }

    scrollToMyRef() {
        setTimeout(() => {
            ReactDOM.findDOMNode(this).scrollIntoView({behavior: "smooth"});
        }, 200);
    }

    renderProjects() {
        return (
            <div className="programmatic-foundation__children programmatic-foundation__projects">
                <div className="programmatic-foundation__items-type">Projets inspirants</div>
                {this.props.measure.projects.map((project, index) => {
                    return <Project
                        key={index+project.uuid}
                        project={project}
                    />
                })}
            </div>
        );
    }

    handleCopyAction(event) {
        event.preventDefault();

        const inputElement = document.createElement('textarea');
        inputElement.value = this.getMeasureUrl(true);
        document.body.appendChild(inputElement);
        inputElement.select();
        document.execCommand('copy');
        document.body.removeChild(inputElement);

        const link = event.target;
        addClass(link, 'copied');

        setTimeout(() => removeClass(link, 'copied'), 2000);
    }

    getMeasureUrl(absolute = false) {
        return `${absolute ? window.location.href : window.location.pathname}/mesures/${this.props.measure.uuid}`
    }

    toggleActiveMeasure(event) {
        if (false === hasClass(event.currentTarget.parentNode, 'expanded')) {
            let items = ReactDOM.findDOMNode(event.currentTarget.closest('.programmatic-foundation__right'))
                .getElementsByClassName('programmatic-foundation__measure');

            for (var i=0; i<items.length; ++i) {
                if (hasClass(items[i], 'expanded')) {
                    removeClass(items[i], 'expanded');
                }
            }
            addClass(event.currentTarget.parentNode, 'expanded');

            this.scrollToMyRef();
        } else {
            removeClass(event.currentTarget.parentNode, 'expanded');
        }
    }
}

Measure.propsType = {
    measure: PropTypes.object.isRequired,
    preventAutoExpand: PropTypes.bool,
};
