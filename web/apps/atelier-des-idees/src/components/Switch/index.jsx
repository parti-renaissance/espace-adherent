import React from 'react';
import PropTypes from 'prop-types';
import RcSwitch from 'rc-switch';
import 'rc-switch/assets/index.css';

/**
 * Override of rc-switch (https://github.com/react-component/switch)
 */
function Switch(props) {
    return <RcSwitch {...props} className="switch" />;
}

Switch.defaultProps = {
    disabled: false,
};

Switch.propTypes = {
    disabled: PropTypes.bool,
    onChange: PropTypes.func.isRequired,
};

export default Switch;
