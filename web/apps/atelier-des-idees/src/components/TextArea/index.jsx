import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

function TextArea(props) {
    return (
        <div className="text-area">
            <textarea
                className={classNames('text-area__input', {
                    'text-area__input--error': props.error,
                })}
                disabled={props.disabled}
                id={props.id}
                maxLength={props.maxlength}
                name={props.name}
                onChange={(e) => {
                    const { value } = e.target;
                    if (
                        !props.maxLength ||
						(props.maxLength && value.length <= props.maxLength)
                    ) {
                        props.onChange(e.target.value);
                    }
                }}
                placeholder={props.placeholder}
                value={props.value}
            >
                {props.value}
            </textarea>
            {props.maxLength && (
                <div className="text-area__counter">{`${props.value.length}/${
                    props.maxLength
                }`}</div>
            )}
            {props.error && <p className="text-area__error">{props.error}</p>}
        </div>
    );
}

TextArea.defaultProps = {
    disabled: false,
    maxLength: undefined,
    placeholder: '',
    value: '',
    error: '',
};

TextArea.propTypes = {
    id: PropTypes.string.isRequired,
    maxLength: PropTypes.number,
    name: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
    placeholder: PropTypes.string,
    value: PropTypes.string,
    error: PropTypes.string,
};

export default TextArea;
