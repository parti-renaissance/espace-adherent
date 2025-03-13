import React from 'react';
import PropTypes from 'prop-types';
import ReactDOM from 'react-dom';
import Measure from './Measure';

export default class SubApproach extends React.Component {
    render() {
        const sectionIdentifierParts = [
            this.props.parentSectionIdentifier,
            this.props.subApproach.position,
        ];

        const sectionIdentifier = sectionIdentifierParts.join('.');

        return (
            <div className={`programmatic-foundation__sub-approach child ${
                this.props.subApproach.isExpanded && !this.props.preventAutoExpand ? 'expanded' : ''
            }`}>
                <div className="head" onClick={this.toggleActiveSubApproach.bind(this)}>
                    <span className="title">{sectionIdentifier} {this.props.subApproach.title}</span>
                    <span className="subtitle">{this.props.subApproach.subtitle}</span>
                    <span className="toggle" />
                </div>

                <div className="content">
                    <div className="html" dangerouslySetInnerHTML={{ __html: this.props.subApproach.content }} />

                    <div className="programmatic-foundation__children programmatic-foundation__measures">
                        <div className="programmatic-foundation__items-type">Mesures</div>
                        {this.props.subApproach.measures.map((measure) => <Measure
                            key={measure.position + measure.uuid}
                            measure={measure}
                        />)}
                    </div>
                </div>
            </div>
        );
    }

    scrollToMyRef() {
        setTimeout(() => {
            ReactDOM.findDOMNode(this).scrollIntoView({ behavior: 'smooth' });
        }, 200);
    }

    toggleActiveSubApproach(event) {
        if (false === hasClass(event.currentTarget.parentNode, 'expanded')) {
            const items = ReactDOM.findDOMNode(event.currentTarget.closest('.programmatic-foundation__right'))
                .getElementsByClassName('programmatic-foundation__sub-approach');

            for (let i = 0; i < items.length; i += 1) {
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

SubApproach.propTypes = {
    subApproach: PropTypes.object.isRequired,
    parentSectionIdentifier: PropTypes.number.isRequired,
};
