import React, {PropTypes} from 'react';

export default class SearchBar extends React.Component {
    render() {
        return (
            <div className="em-form programmatic-foundation__search">
                <div className="em-form__group text-search">
                    <input
                        type="text"
                        className="em-form__field"
                        placeholder="Mesure ou projet illustratif"
                        onChange={event => this.props.onFilterTextChange(event.target.value)}
                        value={this.props.filterText}
                    />
                </div>

                <div className="em-form__group city-search">
                    <select
                        className="em-form__field"
                        onChange={event => this.props.onFilterCityChange(event.target.value)}
                        value={this.props.filterCity}
                    >
                        {this.getCities()}
                    </select>
                </div>
            </div>
        );
    }

    getCities() {
        const cities = [
            <option value="" key="empty-city">Taille de ville</option>,
        ];

        this.props.filterCityChoices.map((city) => {
            cities.push(<option key={city}>{city}</option>);
        });

        return cities;
    }
}

SearchBar.propsType = {
    onFilterTextChange: PropTypes.func.isRequired,
    onFilterCityChange: PropTypes.func.isRequired,

    filterCityChoices: PropTypes.arrayOf(PropTypes.string).isRequired,

    filterText: PropTypes.string,
    filterCity: PropTypes.string,
};

SearchBar.defaultProps = {
    filterText: '',
    filterCity: '',
};
