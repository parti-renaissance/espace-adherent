import React from 'react';

export default class SearchBar extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            query: '',
            city: '',
        };
    }

    handleSearchQueryChange(e) {
        this.props.onSearchChange({
            query: e.target.value,
            city: this.state.city,
        });
    }

    render() {
        return (
            <div className="em-form programmatic-foundation__search">
                <div className="em-form__group text-search">
                    <input type="text" className="em-form__field em-form__search" placeholder="Rechercher une mesure ou un projet illustratif" onChange={this.handleSearchQueryChange.bind(this)} />
                </div>

                <div className="em-form__group city-search">
                    <select className="em-form__field">
                        <option value="">France entière</option>
                        <option value="dog">Dog</option>
                        <option value="cat">Cat</option>
                        <option value="hamster">Hamster</option>
                    </select>
                </div>
            </div>
        );
    }
}
