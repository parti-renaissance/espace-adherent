import React from 'react';
import PropTypes from 'prop-types';

const CtaSection = props => (
    <div className="cta__section">
        <div>
            <h3>{props.title}</h3>
            <p>{props.description}</p>
        </div>
        <div>
            <button onClick={e => props.onClick(e)} className="button__label">
                {props.cta}
            </button>
        </div>
    </div>
);

CtaSection.defaultProps = {
    title: '',
    description: '',
    cta: '',
};

CtaSection.propTypes = {
    title: PropTypes.string,
    description: PropTypes.string,
    cta: PropTypes.string,
    onClick: PropTypes.func.isRequired,
};

export default CtaSection;
