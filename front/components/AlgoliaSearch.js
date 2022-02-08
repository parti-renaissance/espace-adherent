import algoliasearch from 'algoliasearch/lite';
import React from 'react';
import PropTypes from 'prop-types';

export default class AlgoliaSearch extends React.Component {
    constructor(props) {
        super(props);

        const client = algoliasearch(props.appId, props.appKey);

        this.blacklist = props.blacklist || [];

        this.customResultsIndex = client.initIndex(`app_${props.environment}_custom_search_result`);
        this.proposalsIndex = client.initIndex(`app_${props.environment}_proposal`);
        this.clarificationsIndex = client.initIndex(`app_${props.environment}_clarification`);
        this.articlesIndex = client.initIndex(`app_${props.environment}_article`);

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

        if (-1 < this.blacklist.indexOf(term)) {
            this._searchCallback(0, hits);
            return;
        }

        const createResultsHandler = (type) => (content) => {
            loaded += 1;
            nbHits += content.nbHits;
            hits[type] = content.hits.map((hit) => {
                hit.type = type;

                return hit;
            });

            if (Object.keys(hits).length === loaded) {
                this._searchCallback(nbHits, hits);
            }
        };

        this.customResultsIndex.search(term, { hitsPerPage: 15 }).then(createResultsHandler('custom'));
        this.proposalsIndex.search(term, { hitsPerPage: 15 }).then(createResultsHandler('proposal'));
        this.clarificationsIndex.search(term, { hitsPerPage: 15 }).then(createResultsHandler('clarification'));
        this.articlesIndex.search(term, { hitsPerPage: 15 }).then(createResultsHandler('article'));
    }

    _searchCallback(nbHits, hits) {
        const aggregated = []
            .concat(hits.custom)
            .concat(hits.proposal)
            .concat(hits.clarification)
            .concat(hits.article);
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
            return `/articles/${hit.category.slug}/${hit.slug}`;
        }

        if ('proposal' === hit.type) {
            return `/emmanuel-macron/le-programme/${hit.slug}`;
        }

        if ('clarification' === hit.type) {
            return `/desintox/${hit.slug}`;
        }

        return hit.url;
    }

    _createTitle(hit) {
        return hit.title;
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
                                                {this._createTitle(hit)}
                                            </a>
                                        </h2>
                                        <div>
                                            <span dangerouslySetInnerHTML={{ __html: hit.description }} /><br/>
                                            {this._createTypeName(hit)}
                                        </div>
                                        <div className="share">
                                            Partagez
                                            <span role="button"
                                                onClick={() => {
                                                    App.share('facebook', link, this._createTitle(hit));
                                                }}>
                                                <i className="fa fa-facebook-square" />
                                            </span>
                                            <span role="button"
                                                onClick={() => {
                                                    App.share('twitter', link, this._createTitle(hit));
                                                }}>
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
    blacklist: PropTypes.array,
    environment: PropTypes.string.isRequired,
};
