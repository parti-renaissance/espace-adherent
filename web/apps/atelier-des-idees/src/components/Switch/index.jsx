import React from 'react';
import PropTypes from 'prop-types';
import RcSwitch from 'rc-switch';
import 'rc-switch/assets/index.css';

/**
 * Override of rc-switch (https://github.com/react-component/switch)
 */
function Switch(props) {
    return (
        <div className="switch-wrapper">
            <RcSwitch {...props} className="switch" />
            {props.label && <span className="switch__label">{props.label}</span>}
        </div>
    );
}

Switch.defaultProps = {
    disabled: false,
    label: '',
};

Switch.propTypes = {
    disabled: PropTypes.bool,
    label: PropTypes.string,
    onChange: PropTypes.func.isRequired,
};

export default Switch;
