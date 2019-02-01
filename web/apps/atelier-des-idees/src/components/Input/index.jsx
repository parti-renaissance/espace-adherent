import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

function Input(props) {
    return (
        <div
            className={classNames('text-input', props.className, {
                'text-input--error': !!props.error,
            })}
        >
            <input
                className="text-input__input"
                disabled={props.disabled}
                id={props.id}
                name={props.name}
                onChange={e => props.onChange(e.target.value)}
                placeholder={props.placeholder}
                value={props.value}
            />
            {props.error && <p className="text-input__error">{props.error}</p>}
        </div>
    );
}

Input.defaultProps = {
    disabled: false,
    className: '',
    error: '',
    id: '',
    name: '',
    placeholder: '',
};

Input.propTypes = {
    className: PropTypes.string,
    disabled: PropTypes.bool,
    error: PropTypes.string,
    id: PropTypes.string,
    name: PropTypes.string,
    onChange: PropTypes.func.isRequired,
    placeholder: PropTypes.string,
    value: PropTypes.string.isRequired,
};

export default Input;
