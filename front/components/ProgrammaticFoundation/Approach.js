import React from 'react';
import PropTypes from 'prop-types';
import SubApproach from './SubApproach';

export default class Approach extends React.Component {
    render() {
        return (
            <div className="programmatic-foundation__approach">
                <h2>{this.props.approach.position}. {this.props.approach.title}</h2>

                <div className="content" dangerouslySetInnerHTML={{ __html: this.props.approach.content }} />

                <div className="programmatic-foundation__sub-approaches programmatic-foundation__children">
                    {this.props.approach.subApproaches.map((subApproach, index) => <SubApproach
                        key={index + subApproach.uuid}
                        parentSectionIdentifier={this.props.approach.position}
                        subApproach={subApproach}
                    />)}
                </div>
            </div>
        );
    }
}

Approach.propTypes = {
    approach: PropTypes.object.isRequired,
};
