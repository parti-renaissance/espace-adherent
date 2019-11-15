import React, {PropTypes} from 'react';
import _ from 'lodash';
import ReqwestApiClient from '../../services/api/ReqwestApiClient';
import Content from './Content';
import SearchBar from './SearchBar';
import icnClose from './../../../web/images/icons/icn_close.svg';
import Breadcrumbs from './Breadcrumbs';
import ToggleLeadingMeasures from './ToggleLeadingMeasures';
import Loader from '../Loader';

export default class ProgrammaticFoundation extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isLoading: true,
            filterIsLeading: false,
            filterText: '',
            filterCity: '',
            filterTag: '',
        };

        this.handleFilterTextChange = this.handleFilterTextChange.bind(this);
        this.handleFilterCityChange = this.handleFilterCityChange.bind(this);
        this.handleFilterTagChange = this.handleFilterTagChange.bind(this);
        this.handleSearchExit = this.handleSearchExit.bind(this);
        this.handleLeadingMeasuresChange = this.handleLeadingMeasuresChange.bind(this);
    }

    render() {
        const isSearching = this.isSearching();

        return (
            <div>
                <div className="programmatic-foundation__contact information__modal inf-modl--pale-blue b__nudge--top-40">
                    Vous souhaitez partager un projet inspirant ou suggérer une mesure nouvelle ?
                    Vous avez une remarque ou une question sur une mesure ou un projet ?
                    Contactez l'équipe programme à <a href="mailto:idees@en-marche.fr">idees@en-marche.fr</a>
                    <img
                        src={icnClose}
                        className="icn-close"
                        onClick={event => hide(event.target.parentNode)}
                        alt="close icon"
                    />
                </div>

                <SearchBar
                    filterText={this.state.filterText}
                    filterCity={this.state.filterCity}
                    filterTag={this.state.filterTag}
                    filterCityChoices={this.extractAllCities()}
                    filterTagChoices={this.extractAllTags()}
                    onFilterTextChange={this.handleFilterTextChange}
                    onFilterCityChange={this.handleFilterCityChange}
                    onFilterTagChange={this.handleFilterTagChange}
                />

                <Breadcrumbs isSearching={isSearching}  onExitClick={this.handleSearchExit}/>

                {this.state.isLoading ?
                    <Loader title="Chargement..." wrapperClassName="text--body space--30-0 text--center"/> :

                    <div>
                        <h1 className="text--larger b__nudge--bottom-larger">
                            {this.props.isSearching ? 'Recherche...' : 'Le socle programme'}
                        </h1>

                        <div className="l__row l__row--h-stretch l__row--wrap b__nudge--bottom-50">
                            <ToggleLeadingMeasures
                                onToggleChange={this.handleLeadingMeasuresChange}
                                value={this.state.filterIsLeading}
                            />

                            <div className="programmatic-foundation__legend">
                                <span className="legend-title">Légende :</span>
                                <span className="legend-item basic-measure">Mesure</span>
                                <span className="legend-item leading-measure">Mesure phare</span>
                                <span className="legend-item project">Projet illustratif</span>
                            </div>
                        </div>

                        <Content
                            isSearching={isSearching}
                            filterIsLeading={this.state.filterIsLeading}
                            filterText={this.state.filterText}
                            filterCity={this.state.filterCity}
                            filterTag={this.state.filterTag}
                            approaches={this.initialApproaches}
                        />
                    </div>
                }
            </div>
        );
    }

    componentDidMount() {
        this.props.api.getApproaches((approaches) => {
            this.initialApproaches = approaches;

            this.setState({
                // approaches,
                isLoading: false,
            });
        });
    }

    handleFilterTextChange(text) {
        this.setState({filterText: text})
    }

    handleFilterCityChange(text) {
        this.setState({filterCity: text})
    }

    handleFilterTagChange(text) {
        this.setState({filterTag: text})
    }

    handleLeadingMeasuresChange(value) {
        this.setState({filterIsLeading: value})
    }

    extractAllCities() {
        return _.uniq(_.flatMap(this.initialApproaches, (approach) => {
            return _.flatMap(approach.sub_approaches, (subApproaches) => {
                return _.flatMap(subApproaches.measures, (measure) => {
                    return _.flatMap(measure.projects, (project) => {
                        return project.city;
                    });
                });
            });
        })).sort();
    }

    extractAllTags() {
        return _.uniq(_.flatMap(this.initialApproaches, (approach) => {
            return _.flatMap(approach.sub_approaches, (subApproaches) => {
                return _.flatMap(subApproaches.measures, measure => {
                    return _.merge(
                        _.flatMap(measure.tags, tag => tag.label),
                        _.flatMap(measure.projects, project => _.flatMap(project.tags, tag => tag.label))
                    );
                });
            });
        })).sort();
    }

    isSearching() {
        return !!this.state.filterText || !!this.state.filterCity || !!this.state.filterTag;
    }

    handleSearchExit(event) {
        event.preventDefault();

        this.setState({
            filterText: '',
            filterCity: '',
            filterTag: '',
            filterIsLeading: false,
        })
    }
}

ProgrammaticFoundation.propTypes = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
};
