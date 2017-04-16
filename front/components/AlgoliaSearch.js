import algoliasearch from 'algoliasearch';
import React, { PropTypes } from 'react';

export default class AlgoliaSearch extends React.Component {
    constructor(props) {
        super(props);

        const client = algoliasearch(props.appId, props.appKey);

        this.customResultsIndex = client.initIndex(`CustomSearchResult_${props.environment}`);
        this.proposalsIndex = client.initIndex(`Proposal_${props.environment}`);
        this.clarificationsIndex = client.initIndex(`Clarification_${props.environment}`);
        this.articlesIndex = client.initIndex(`Article_${props.environment}`);

        this.state = {
            term: '',
            loading: false,
            hits: [],
            nbHits: 0,
        };

        this.timer = null;
        this.handleTermChange = this.handleTermChange.bind(this);
        this.handleKeyPress = this.handleKeyPress.bind(this);
    }

    handleTermChange(event) {
        const term = event.target.value;

        this.setState({
            term,
            loading: true,
        });

        clearTimeout(this.timer);
        this.timer = setTimeout(() => { this._search(term); }, 200);
    }

    handleKeyPress(event) {
        // Disable enter
        const code = event.which || event.keyCode;

        if (13 === code || 10 === code) {
            event.preventDefault();
        }
    }

    _search(term) {
        let loaded = 0;
        let nbHits = 0;

        const hits = {
            custom: [],
            proposal: [],
            clarification: [],
            article: [],
        };

        const createResultsHandler = type => (err, content) => {
            loaded += 1;
            nbHits += content.nbHits;
            hits[type] = content.hits.map((hit) => {
                hit.type = type;

                return hit;
            });

            if (4 === loaded) {
                this._searchCallback(nbHits, hits);
            }
        };

        this.customResultsIndex.search({ query: term, hitsPerPage: 15 }, createResultsHandler('custom'));
        this.proposalsIndex.search({ query: term, hitsPerPage: 15 }, createResultsHandler('proposal'));
        this.clarificationsIndex.search({ query: term, hitsPerPage: 15 }, createResultsHandler('clarification'));
        this.articlesIndex.search({ query: term, hitsPerPage: 15 }, createResultsHandler('article'));
    }

    _searchCallback(nbHits, hits) {
        const aggregated = []
            .concat(hits.custom)
            .concat(hits.proposal)
            .concat(hits.clarification)
            .concat(hits.article)
        ;

        this.setState({
            loading: false,
            hits: aggregated,
            nbHits,
        });
    }

    _createImageURL(hit) {
        if ('custom' === hit.type) {
            return `/algolia/custom/${hit.id}`;
        }

        return `/algolia/${hit.type}/${hit.slug}`;
    }

    _createTypeName(hit) {
        if ('proposal' === hit.type) {
            return 'Proposition du programme';
        }

        if ('clarification' === hit.type) {
            return 'Désintox';
        }

        if ('article' === hit.type) {
            return 'Actualité';
        }

        return '';
    }

    _createLinkURL(hit) {
        if ('article' === hit.type) {
            return `/article/${hit.slug}`;
        }

        if ('proposal' === hit.type) {
            return `/emmanuel-macron/le-programme/${hit.slug}`;
        }

        if ('clarification' === hit.type) {
            return `/emmanuel-macron/desintox/${hit.slug}`;
        }

        return hit.url;
    }

    render() {
        const loadingStyle = this.state.loading ? { opacity: 0.2 } : {};

        return (
            <div className="g-search__content text--body listing">
                <div className="g-search__search--outer">
                    <div className="g-search__search l__wrapper--slim text--center">
                        <form>
                            <input type="text" placeholder="Rechercher" id="search-input"
                                   onChange={this.handleTermChange}
                                   onKeyPress={this.handleKeyPress} />
                        </form>
                        <div className="b__nudge--top">
                            {`${this.state.nbHits} résultat${1 < this.state.nbHits ? 's' : ''}`}
                        </div>
                    </div>
                </div>

                <article className="g-search__results l__wrapper--narrow" style={loadingStyle}>
                    <ul>
                        {this.state.hits.map((hit, i) => {
                            const link = this._createLinkURL(hit);

                            return (
                                <li key={`${i}-${hit.objectID}`}>
                                    <a href={link} className="thumbnail">
                                        <img src={this._createImageURL(hit)} title={hit.title} alt={hit.title} />
                                    </a>
                                    <div>
                                        <h2>
                                            <a href={link}>
                                                {hit.title}
                                            </a>
                                        </h2>
                                        <div>
                                            {this._createTypeName(hit)}
                                        </div>
                                        <div className="share">
                                            Partagez
                                            <span role="button"
                                                  onClick={() => { App.share('facebook', link, hit.title); }}>
                                                <i className="fa fa-facebook-square" />
                                            </span>
                                            <span role="button"
                                                  onClick={() => { App.share('twitter', link, hit.title); }}>
                                                <i className="fa fa-twitter" />
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            );
                        })}
                    </ul>
                </article>
            </div>
        );
    }
}

AlgoliaSearch.propTypes = {
    appId: PropTypes.string.isRequired,
    appKey: PropTypes.string.isRequired,
    environment: PropTypes.string.isRequired,
};
