import React, { Component } from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

class TextArea extends Component {
	state = {
		value: ''
	};

	render() {
		return (
			<div className="text-area">
				<div className="text-area__input-wrapper">
					<textarea
						className={classNames('text-area__input', {
							'text-area__input--error': this.props.error
						})}
						disabled={this.props.disabled}
						id={this.props.id}
						maxLength={this.props.maxLength}
						name={this.props.name}
						onChange={e => {
							const { value } = e.target;
							if (
								!this.props.maxLength ||
								(this.props.maxLength && value.length <= this.props.maxLength)
							) {
								this.props.onChange(e.target.value);
							}
						}}
						placeholder={this.props.placeholder}
						value={this.props.value}
						autoFocus={this.props.autofocus}
					/>
					{this.props.maxLength && (
						<div className="text-area__counter">{`${this.props.value.length}/${this.props.maxLength}`}</div>
					)}
				</div>

				{this.props.error && <p className="text-area__error">{this.props.error}</p>}
			</div>
		);
	}
}

TextArea.defaultProps = {
	id: '',
	disabled: false,
	maxLength: undefined,
	placeholder: '',
	autofocus: false,
	value: '',
	name: '',
	error: ''
};

TextArea.propTypes = {
	id: PropTypes.string,
	maxLength: PropTypes.number,
	name: PropTypes.string,
	onChange: PropTypes.func.isRequired,
	placeholder: PropTypes.string,
	autofocus: PropTypes.bool,
	value: PropTypes.string,
	error: PropTypes.string
};

export default TextArea;
