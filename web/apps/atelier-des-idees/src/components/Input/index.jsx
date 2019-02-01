import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

class Input extends React.Component {
    handleChange(value) {
        this.props.onChange(value);
    }

    render() {
        return (
            <div
                className={classNames('text-input', this.props.className, {
                    'text-input--error': !!this.props.error,
                    'text-input--count': !!this.props.maxLength,
                })}
            >
                <div className="text-input__input-wrapper">
                    <input
                        className="text-input__input"
                        disabled={this.props.disabled}
                        id={this.props.id}
                        maxLength={this.props.maxLength}
                        name={this.props.name}
                        onChange={e => this.handleChange(e.target.value)}
                        placeholder={this.props.placeholder}
                        value={this.props.value}
                    />
                    {this.props.maxLength && (
                        <div className="text-input__counter">{`${this.props.value.length} / ${
                            this.props.maxLength
                        }`}</div>
                    )}
                </div>
                {this.props.error && <p className="text-input__error">{this.props.error}</p>}
            </div>
        );
    }
}

Input.defaultProps = {
    disabled: false,
    className: '',
    error: '',
    id: '',
    maxLength: undefined,
    name: '',
    placeholder: '',
};

Input.propTypes = {
    className: PropTypes.string,
    disabled: PropTypes.bool,
    error: PropTypes.string,
    id: PropTypes.string,
    maxLength: PropTypes.number,
    name: PropTypes.string,
    onChange: PropTypes.func.isRequired,
    placeholder: PropTypes.string,
    value: PropTypes.string.isRequired,
};

export default Input;
