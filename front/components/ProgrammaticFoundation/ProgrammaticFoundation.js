import React from 'react';
import Approaches from './Approaches';
import ToggleLeadingMeasures from './ToggleLeadingMeasures';
import SearchBar from './SearchBar';
import SearchResults from './SearchResults';
import Filterer from '../../services/programmatic-foundation/Filterer';
import SearchEngine from '../../services/programmatic-foundation/SearchEngine';
import Legend from './Legend';

export default class ProgrammaticFoundation extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            loading: true,
            approaches: [],
            searching: false,
            searchResults: [],
        };

        props.api.getApproaches((approaches) => {
            this.unfilteredApproaches = approaches;
            this.setState({
                approaches,
                loading: false,
            });
        });
    }

    handleLeadingMeasuresChange(isLeading) {
        if (this.state.searching) {
            this.setState({
                searchResults: Filterer.filterSearchResultsByIsLeading(isLeading, this.unfilteredSearchResults),
            });
        } else {
            this.setState({
                approaches: Filterer.filterApproachesByIsLeading(isLeading, this.unfilteredApproaches),
            });
        }
    }

    handleSearchChange(searchData) {
        const query = searchData.query;
        const city = searchData.city;

        if (!query.length && !city.length) {
            this.setState({
                searching: false,
            });

            return;
        }

        this.unfilteredSearchResults = SearchEngine.search(query, city, this.unfilteredApproaches);

        this.setState({
            searching: true,
            searchResults: this.unfilteredSearchResults,
        });
    }

    render() {
        return (
            <div>
              {this.renderSearchBar()}
              <div className="programmatic-foundation__breadcrumb">{this.state.searching ? 'Quitter la recherche' : 'Socle programme > Toutes les mesures'}</div>
              <h1 className="text--larger b__nudge--bottom-larger">{this.state.searching ? 'Recherche' : 'Le socle programme'}</h1>
              {this.renderContent()}
            </div>
        );
    }

    renderSearchBar() {
        if (this.state.loading) {
            return null;
        }

        return <SearchBar onSearchChange={v => this.handleSearchChange(v)}/>;
    }

    renderContent() {
        if (this.state.loading) {
            return <p className="text--body">Chargement...</p>;
        }

        return (
            <div>
                <div className="l__row l__row--h-stretch l__row--wrap b__nudge--bottom-50">
                    <ToggleLeadingMeasures onToggleChange={v => this.handleLeadingMeasuresChange(v)} />
                    <Legend />
                </div>

                {
                  this.state.searching ? <SearchResults results={this.state.searchResults} />
                  : <Approaches approaches={this.state.approaches}/>
                }
            </div>
        );
    }
}
