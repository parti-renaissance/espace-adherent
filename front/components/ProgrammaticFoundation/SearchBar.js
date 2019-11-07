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
            <div>
              <input type="text" onChange={this.handleSearchQueryChange.bind(this)} />
            </div>
        );
    }
}
