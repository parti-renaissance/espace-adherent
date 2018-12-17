import React from 'react';
import PropTypes from 'prop-types';

function Button(props) {
    return (
        <button
            className="button"
            aria-label={props.label}
            disabled={props.disabled}
            onClick={() => props.onClick()}>
            {props.label}
        </button>
    );
}

Button.defaultProps = {
    label: undefined,
    icon: undefined,
    type: 'button',
    disabled: false,
};

Button.propTypes = {
    label: PropTypes.string,
    icon: PropTypes.string,
    type: PropTypes.string,
    onClick: PropTypes.func.isRequired,
    disabled: PropTypes.bool,
};

export default Button;