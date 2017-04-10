import algoliasearch from 'algoliasearch';
import React, { PropTypes } from 'react';

export default class AlgoliaSearch extends React.Component {
    constructor(props) {
        super(props);

        const client = algoliasearch(props.appId, props.appKey);

        this.articlesIndex = client.initIndex('Article_dev');
        this.pagesIndex = client.initIndex('Page_dev');
        this.proposalsIndex = client.initIndex('Proposal_dev');

        this.state = {
            term: '',
            loading: false,
            hits: [],
            nbHits: 0,
        };

        this.timer = null;
        this.handleTermChange = this.handleTermChange.bind(this);
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

    _search(term) {
        let loaded = 0;
        let hits = [];
        let nbHits = 0;

        const createResultsHandler = type => (err, content) => {
            loaded += 1;
            nbHits += content.nbHits;

            hits = hits.concat(content.hits.map((hit) => {
                hit.type = type;

                return hit;
            }));

            if (3 === loaded) {
                this._searchCallback(nbHits, hits);
            }
        };

        this.proposalsIndex.search({ query: term, hitsPerPage: 15 }, createResultsHandler('proposal'));
        this.articlesIndex.search({ query: term, hitsPerPage: 15 }, createResultsHandler('article'));
        this.pagesIndex.search({ query: term, hitsPerPage: 15 }, createResultsHandler('page'));
    }

    _searchCallback(nbHits, hits) {
        this.setState({
            loading: false,
            hits,
            nbHits,
        });
    }

    _createImageURL(hit) {
        return `/algolia/${hit.type}/${hit.slug}`;
    }

    _createTypeName(hit) {
        if ('proposal' === hit.type) {
            return 'Proposition du programme';
        }

        if ('article' === hit.type) {
            return 'Actualité';
        }

        return '';
    }

    _createLinkURL(hit) {
        if ('page' === hit.type) {
            return hit.url;
        }

        if ('proposal' === hit.type) {
            return `/emmanuel-macron/le-programme/${hit.slug}`;
        }

        return `/article/${hit.slug}`;
    }

    render() {
        const loadingStyle = this.state.loading ? { opacity: 0.2 } : {};

        return (
            <div className="g-search__content text--body listing">
                <div className="g-search__search l__wrapper--slim text--center b__nudge--bottom-large">
                    <form>
                        <input type="text" placeholder="Rechercher" id="search-input"
                               onChange={this.handleTermChange} />

                        <div className="btn btn--large btn--no-border">
                            <i className="fa fa-search" />
                        </div>
                    </form>
                    <div className="b__nudge--top">
                        {`${this.state.nbHits} résultat${1 < this.state.nbHits ? 's' : ''}`}
                    </div>
                </div>

                <article className="g-search__results l__wrapper--narrow" style={loadingStyle}>
                    <ul>
                        {this.state.hits.map((hit) => {
                            const link = this._createLinkURL(hit);

                            return (
                                <li key={`${this.state.term}-${hit.objectID}`}>
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
};
