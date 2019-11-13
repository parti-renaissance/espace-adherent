import React, {PropTypes} from 'react';
import _ from 'lodash';
import ToggleLeadingMeasures from './ToggleLeadingMeasures';
import SearchBar from './SearchBar';
import SearchResults from './SearchResults';
import SearchEngine from '../../services/programmatic-foundation/SearchEngine';
import ReqwestApiClient from '../../services/api/ReqwestApiClient';
import Loader from '../Loader';
import Approach from './Approach';

import icnClose from './../../../web/images/icons/icn_close.svg';

export default class ProgrammaticFoundation extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            searchBarKey: 1,
            loading: true,
            searching: false,
            filters: null,
            approaches: [],
            searchResults: [],
        };

        this.handleLeadingMeasuresChange = this.handleLeadingMeasuresChange.bind(this);
        this.handleSearchChange = this.handleSearchChange.bind(this);
    }

    componentDidMount() {
        this.props.api.getApproaches((approaches) => {
            this.rawApproaches = approaches;

            this.setState({
                approaches,
                loading: false,
            });
        });
    }

    handleLeadingMeasuresChange(isLeading) {
        if (this.state.searching) {
            this.handleSearchChange({...this.state.filters, ...{isLeading: isLeading}});
        } else {
            this.setState({
                approaches: this.filterApproachesByIsLeading(isLeading),
            });
        }
    }

    handleSearchChange(data) {
        const searching = !!data.query || !!data.city;

        this.setState({
            searching: searching,
            searchResults: searching ? SearchEngine.search(this.state.approaches, data) : [],
            filters: data,
        });
    }

    render() {
        return (
            <div>
                <div className="programmatic-foundation__contact information__modal inf-modl--pale-blue b__nudge--top-40">
                    Vous souhaitez partager un projet inspirant ou suggérer une mesure nouvelle ?
                    Vous avez une remarque ou une question sur une mesure ou un projet ?
                    Contactez l'équipe programme à <a href="mailto:idees@en-marche.fr">idees@en-marche.fr</a>
                    <img src={icnClose} className="icn-close" />
                </div>

                <SearchBar
                    key={`is-active-${this.state.searchBarKey}`}
                    onSearchChange={this.handleSearchChange}
                    filters={this.state.filters}
                    cityChoices={this.getCitiesFromProjects()}
                />

                {this.state.loading ?
                    <Loader title="Chargement..." wrapperClassName="text--body space--30-0 text--center"/> :
                    this.renderApproaches()
                }
            </div>
        );
    }

    renderApproaches() {
        return (
            <div>
                {this.renderBreadcrumbs()}

                <h1 className="text--larger b__nudge--bottom-larger">{this.state.searching ? 'Recherche...' : 'Le socle programme'}</h1>

                <div className="l__row l__row--h-stretch l__row--wrap b__nudge--bottom-50">
                    <ToggleLeadingMeasures key={`active-${this.state.filters && this.state.filters.isLeading}`} onToggleChange={this.handleLeadingMeasuresChange} value={this.state.filters && this.state.filters.isLeading}/>

                    <div className="programmatic-foundation__legend">
                        <span className="legend-title">Légende :</span>
                        <span className="legend-item basic-measure">Mesure</span>
                        <span className="legend-item leading-measure">Mesure phare</span>
                        <span className="legend-item project">Projet illustratif</span>
                    </div>
                </div>

                {this.state.searching ?
                    <SearchResults results={this.state.searchResults} /> :
                    <div className="programmatic-foundation__approaches">
                        {this.state.approaches.map((approach, index) => {
                            return <Approach key={index} approach={approach}/>;
                        })}
                    </div>
                }
            </div>
        );
    }

    getCitiesFromProjects() {
        return _.uniq(_.flatMap(this.state.approaches, (approach) => {
            return _.flatMap(approach.sub_approaches, (subApproaches) => {
                return _.flatMap(subApproaches.measures, (measure) => {
                    return _.flatMap(measure.projects, (project) => {
                        return project.city;
                    });
                });
            });
        })).sort();
    }

    renderBreadcrumbs() {
        const breadcrumbParts = [];

        if (this.state.searching) {
            breadcrumbParts.push(<a href={'#'} className={"link--no--decor"} onClick={event => {
                event.preventDefault();
                this.setState({searchBarKey: this.state.searchBarKey + 1});
                this.handleSearchChange({});
            }}>⟵ Quitter la recherche</a>)
        } else {
            breadcrumbParts.push('Socle programme', 'Toutes les mesures')
        }

        return <ul className="programmatic-foundation__breadcrumb text--body">
            {breadcrumbParts.map((item, index) => <li key={index}>{item}</li>)}
        </ul>
    }

    filterApproachesByIsLeading(isLeading) {
        if (isLeading === false) {
            return this.rawApproaches;
        }

        return _.filter(this.rawApproaches, (approach) => {
            const subApproaches = _.filter(approach.sub_approaches, (sub_approach) => {
                const measures = _.filter(sub_approach.measures, (measure) => { return measure.isLeading;});

                if (measures.length) {
                    sub_approach.measures = measures;
                }

                return !!measures.length;
            });

            if (subApproaches.length) {
                approach.sub_approaches = subApproaches;
            }

            return !!subApproaches.length;
        });
    }
}

ProgrammaticFoundation.propsType = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
};
