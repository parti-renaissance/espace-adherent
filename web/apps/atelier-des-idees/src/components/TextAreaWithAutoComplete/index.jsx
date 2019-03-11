import React, { Component } from 'react';
import AutoComplete from './../Autocomplete';
import ClickOutside from './ClickOutside';
import PropTypes from 'prop-types';
import classNames from 'classnames';

class TextAreaWithAutoComplete extends Component {
  state = {
    value: '',
    autoCompleteIsOpen: false,
    autoCompleteIsDisabled: false
  };

  handleChange(e) {
    this.showAutoComplete(e);
    const { value } = e.target;
    if (!this.props.maxLength || (this.props.maxLength && value.length <= this.props.maxLength)) {
      this.props.onChange(e.target.value);
    }
  }
  showAutoComplete(e) {
    if (!this.state.autoCompleteIsOpen && !this.state.autoCompleteIsDisabled) {
      this.setState({ autoCompleteIsOpen: true });
    } else return;
  }
  hideComplete(e) {
    if (this.state.autoCompleteIsOpen) {
      this.setState({ autoCompleteIsOpen: false });
    } else return;
  }
  disableAutoComplete(e) {
    this.hideComplete(e);
    this.setState({ autoCompleteIsDisabled: true });
  }

  render() {
    return (
      <ClickOutside onClick={e => this.hideComplete(e)}>
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
              onChange={e => this.handleChange(e)}
              onFocus={e => this.showAutoComplete(e)}
              placeholder={this.props.placeholder}
              value={this.props.value}
              autoFocus={this.props.autofocus}
              data-selectlist={this.props.dataSelectlist}
              data-prev={this.props.dataPrev}
              data-next={this.props.dataNext}
            />
            {this.props.maxLength && (
              <div className="text-area__counter">{`${this.props.value.length}/${this.props.maxLength}`}</div>
            )}
          </div>
          {this.props.haveAutoComplete && 1 <= this.props.value.length && this.state.autoCompleteIsOpen && (
            <AutoComplete
              options={this.props.autoCompleteValues}
              value={this.state.value}
              onClick={e => this.disableAutoComplete(e)}
            />
          )}
          {this.props.error && <p className="text-area__error">{this.props.error}</p>}
        </div>
      </ClickOutside>
    );
  }
}

TextAreaWithAutoComplete.defaultProps = {
  id: '',
  disabled: false,
  maxLength: undefined,
  placeholder: '',
  autofocus: false,
  value: '',
  name: '',
  error: '',
  dataSelectlist: '',
  dataPrev: '',
  dataNext: ''
};

TextAreaWithAutoComplete.propTypes = {
  id: PropTypes.string,
  maxLength: PropTypes.number,
  name: PropTypes.string,
  onChange: PropTypes.func.isRequired,
  placeholder: PropTypes.string,
  autofocus: PropTypes.bool,
  value: PropTypes.string,
  error: PropTypes.string,
  haveAutoComplete: PropTypes.bool,
  dataSelectlist: PropTypes.string,
  dataPrev: PropTypes.string,
  dataNext: PropTypes.string
};

export default TextAreaWithAutoComplete;
