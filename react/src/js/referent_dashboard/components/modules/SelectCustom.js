import React, { Component } from 'react';
import PropTypes from 'prop-types';

import Select from 'react-select';

const RadioBox = ({ value, onChange, selected, label }) => (
    <div>
        <label>
            <input type="radio" value={value} onChange={onChange} checked={selected === value} />
            {label}
        </label>
    </div>
);

class SelectCustom extends Component {
    state = { selectedBox: 'committee', selectedOption: '', options: [] };

    setSelectedBox = (e) => {
        this.setState({ selectedBox: e.target.value, currentFilter: null });
    };

    getOptions = () => {
        const options = [];
        if ('committee' === this.state.selectedBox) {
            for (const committee of this.props.autocomplete.committees) {
                options.push({
                    value: Object.keys(committee)[0],
                    label: Object.values(committee)[0],
                });
            }
        } else if ('city' === this.state.selectedBox) {
            for (const city of this.props.autocomplete.cities) {
                options.push({
                    value: city,
                    label: city,
                });
            }
        } else if ('countries' === this.state.selectedBox) {
            for (const country of this.props.autocomplete.countries) {
                options.push({
                    value: Object.keys(country)[0],
                    label: Object.values(country)[0],
                });
            }
        }
        return options;
    };

    onInputChange = (value) => {
        if (this.props.autocompletePending) {
            return;
        }
        if ('' === value) {
            this.props.onSelect('');
            return;
        }
        this.setState({ selectedOption: value });
        this.props.autocompleteSearch(this.state.selectedBox, value);
    };

    onInputSelect = (e) => {
        if (null === e) {
            this.props.onFilter('');
            this.setState({ selectedOption: null });
        } else {
            this.setState({ selectedOption: e.value });
            this.props.onSelect(`?${this.state.selectedBox}=${e.value}`);
            if ('committee' === this.state.selectedBox) {
                for (const committee of this.props.autocomplete.committees) {
                    if (e.value in committee) {
                        this.props.onFilter(committee[e.value]);
                    }
                }
            } else if ('city' === this.state.selectedBox) {
                this.props.onFilter(e.value);
            }
        }
    };

    getPlaceholder = () => {
        const placeholders = {
            committee: 'Rechercher un comité',
            city: 'Rechercher une ville',
        };
        return placeholders[this.state.selectedBox];
    };

    render() {
        const options = this.getOptions();

        return (
            <div>
                <RadioBox
                    value="committee"
                    onChange={this.setSelectedBox}
                    label="Comité"
                    selected={this.state.selectedBox}
                />
                <RadioBox value="city" onChange={this.setSelectedBox} label="Ville" selected={this.state.selectedBox} />
                <div style={{ minWidth: '300px' }}>
                    <Select
                        clearable={true}
                        onSelectResetsInput={false}
                        placeholder={this.getPlaceholder()}
                        name="autocomplete-select"
                        value={this.state.selectedOption}
                        onInputChange={this.onInputChange}
                        onChange={this.onInputSelect}
                        options={options}
                    />
                </div>
            </div>
        );
    }
}

export default SelectCustom;

SelectCustom.propTypes = {
    id: PropTypes.string,
    name: PropTypes.string,
    value: PropTypes.string,
    key: PropTypes.number,
    onChange: PropTypes.func,
};
