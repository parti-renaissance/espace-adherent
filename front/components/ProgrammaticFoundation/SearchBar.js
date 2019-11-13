import React, {PropTypes} from 'react';

export default class SearchBar extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            query: (props.filters && props.filters.query) || '',
            city: (props.filters && props.filters.city) || '',
        };
    }

    handleFilterChange(data) {
        this.props.onSearchChange({...this.state, ...data});

        this.setState(data);
    }

    render() {
        return (
            <div className="em-form programmatic-foundation__search">
                <div className="em-form__group text-search">
                    <input
                        type="text"
                        className="em-form__field em-form__search"
                        placeholder="Rechercher une mesure ou un projet illustratif"
                        onChange={(event) => this.handleFilterChange({query: event.target.value})}
                        value={this.state.query}
                    />
                </div>

                <div className="em-form__group city-search">
                    <select
                        className="em-form__field"
                        onChange={(event) => this.handleFilterChange({city: event.target.value})}
                        value={this.state.city}
                    >
                        <option value="">France entière</option>
                        {this.props.cityChoices.map(city => {
                            return <option key={city}>{city}</option>;
                        })}
                    </select>
                </div>
            </div>
        );
    }
}

SearchBar.propsType = {
    onSearchChange: PropTypes.func.isRequired,
    cityChoices: PropTypes.arrayOf(PropTypes.string).isRequired,
    filters: PropTypes.object,
};
