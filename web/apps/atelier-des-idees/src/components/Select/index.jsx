import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import SelectComponent, { components } from 'react-select';

const DropdownIndicator = props =>
    components.DropdownIndicator && (
        <components.DropdownIndicator {...props}>
            <span className="dropdown" />
        </components.DropdownIndicator>
    );

const MultiValueLabel = props => <components.MultiValueLabel {...props} />;

const MultiValueRemove = props => <components.MultiValueRemove {...props} />;

class Select extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            selectedOption: this.props.defaultValue,
            errorMaxOptions: '',
        };
        this.handleChange = this.handleChange.bind(this);
    }

    // @param {object, array} selectedOption object by default and array if multi-select
    handleChange(selectedOption) {
        // check if max options selected
        const isMaxOptionsSelected = selectedOption && selectedOption.length > this.props.maxOptionsSelected;

        if (isMaxOptionsSelected) {
            // set the error
            this.setState({
                errorMaxOptions: `Vous ne pouvez sélectionner que ${this.props.maxOptionsSelected} ${this.props
                    .maxOptionsLabel || 'option'}${1 < this.props.maxOptionsSelected ? 's' : ''}`,
            });
            // remove the option that is too much
            selectedOption.pop();
        } else {
            // remove error
            this.setState({
                errorMaxOptions: '',
            });
        }

        this.setState({ selectedOption });

        const formatSelectedOption = this.props.isMulti ? selectedOption : [selectedOption];

        this.props.onSelected(formatSelectedOption);
    }

    render() {
        const SubTitle = this.props.subtitle;
        return (
            <div className="select">
                <SelectComponent
                    className={classNames('select__input', { 'select__input--multi': this.props.isMulti })}
                    classNamePrefix="select__input"
                    value={this.state.selectedOption}
                    components={{ DropdownIndicator, MultiValueLabel, MultiValueRemove }}
                    styles={{
                        multiValueLabel: base => ({
                            ...base,
                            backgroundColor: '#d2ecff',
                            color: '#0496ff',
                        }),
                        multiValueRemove: base => ({
                            ...base,
                            backgroundColor: '#d2ecff',
                            color: '#0496ff',
                        }),
                        control: base => ({
                            ...base,
                            borderColor: this.props.error || this.state.errorMaxOptions ? '#ff4a22' : '#e5e5e5',
                        }),
                    }}
                    onChange={this.handleChange}
                    options={this.props.options}
                    placeholder={this.props.placeholder}
                    isClearable={this.props.isClearable}
                    isDisabled={this.props.isDisabled}
                    isMulti={this.props.isMulti}
                    isSearchable={this.props.isSearchable}
                    defaultValue={this.props.defaultValue}
                    noOptionsMessage={() => 'Pas de résultat'}
                />
                {this.props.subtitle && (
                    <div className="select__subtitle">{'function' === typeof SubTitle ? <SubTitle /> : SubTitle}</div>
                )}

                {this.props.error && <p className="select__error">{this.props.error}</p>}
                {this.state.errorMaxOptions && <p className="select__error">{this.state.errorMaxOptions}</p>}
            </div>
        );
    }
}

Select.defaultProps = {
    subtitle: '',
    error: '',
    isMulti: false,
    placeholder: '',
    isClearable: false,
    isDisabled: false,
    isSearchable: false,
    defaultValue: undefined,
    maxOptionsSelected: undefined,
    maxOptionsLabel: '',
};

Select.propTypes = {
    subtitle: PropTypes.oneOfType([PropTypes.node.isRequired, PropTypes.func, PropTypes.string]),
    error: PropTypes.string,
    options: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.string.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    placeholder: PropTypes.string,
    isClearable: PropTypes.bool,
    isDisabled: PropTypes.bool,
    isMulti: PropTypes.bool,
    isSearchable: PropTypes.bool,
    onSelected: PropTypes.func.isRequired,
    defaultValue: PropTypes.oneOf([
        PropTypes.shape({
            value: PropTypes.string.isRequired,
            label: PropTypes.string.isRequired,
        }),
        PropTypes.arrayOf(
            PropTypes.shape({
                value: PropTypes.string.isRequired,
                label: PropTypes.string.isRequired,
            })
        ),
    ]),
    maxOptionsSelected: PropTypes.number,
    maxOptionsLabel: PropTypes.string,
};

export default Select;
