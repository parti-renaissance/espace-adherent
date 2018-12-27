import React from 'react';
import PropTypes from 'prop-types';
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
            selectedOption: null,
        };
        this.handleChange = this.handleChange.bind(this);
    }

    // @param {object, array} selectedOption object by default and array if multi-select
    handleChange(selectedOption) {
        this.setState({ selectedOption });

        const formatSelectedOption = this.props.isMulti
            ? selectedOption.map(selected => selected.value)
            : [selectedOption.value];

        this.props.onSelected(formatSelectedOption);
    }

    render() {
        return (
            <div className="select">
                <SelectComponent
                    className="select__input"
                    value={this.state.selectedOption}
                    defaultValue={this.props.defaultValue}
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
                            borderColor: this.props.error ? '#ff4a22' : '#e5e5e5',
                        }),
                    }}
                    onChange={this.handleChange}
                    options={this.props.options}
                    placeholder={this.props.placeholder}
                    isClearable={this.props.isClearable}
                    isDisabled={this.props.isDisabled}
                    isMulti={this.props.isMulti}
                />
                {this.props.subtitle && (
                    <p className="select__subtitle">{this.props.subtitle}</p>
                )}

                {this.props.error && (
                    <p className="select__error">{this.props.error}</p>
                )}
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
};

Select.propTypes = {
    subtitle: PropTypes.string,
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
    onSelected: PropTypes.func.isRequired,
};

export default Select;
