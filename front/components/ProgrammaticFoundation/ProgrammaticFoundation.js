import React, {PropTypes} from 'react';
import _ from 'lodash';
import ReqwestApiClient from '../../services/api/ReqwestApiClient';
import Content from './Content';
import SearchBar from './SearchBar';
import icnClose from './../../../public/images/icons/icn_close.svg';
import logoPQM from './../../../public/images/projets-qui-marchent-logo-horizontal.svg';
import Breadcrumbs from './Breadcrumbs';
import Loader from '../Loader';
import ReactDOM from "react-dom";

export default class ProgrammaticFoundation extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isLoading: true,
            filterIsLeading: false,
            filterText: '',
            filterCity: '',
        };

        this.handleFilterTextChange = this.handleFilterTextChange.bind(this);
        this.handleFilterCityChange = this.handleFilterCityChange.bind(this);
        this.handleSearchExit = this.handleSearchExit.bind(this);
        this.handleLeadingMeasuresChange = this.handleLeadingMeasuresChange.bind(this);
    }

    render() {
        const isSearching = this.isSearching();

        return (
            <div className="programmatic-foundation__row l__wrapper">
                <span className="background-stripe-02"></span>
                <div className="programmatic-foundation__left">
                    <div className="content l__col">
                        <a href={"/projets-qui-marchent"} className="socle-logo">
                            <img
                                src={logoPQM}
                                alt="Socle programmatique - Des projets qui marchent"
                            />
                        </a>

                        <div className="text--body b__nudge--top b__nudge--bottom"><strong>Filtrer</strong></div>

                        <SearchBar
                            filterText={this.state.filterText}
                            filterCity={this.state.filterCity}
                            filterCityChoices={this.extractAllCities()}
                            onFilterTextChange={this.handleFilterTextChange}
                            onFilterCityChange={this.handleFilterCityChange}
                        />

                        <div className="programmatic-foundation__contact">
                            Vous souhaitez partager un projet illustratif ou suggérer une mesure nouvelle ?
                            Vous avez une remarque ou une question sur une mesure ou un projet ?
                            Contactez l'équipe programme à <a className="text--blue--dark link--no-decor" href="mailto:idees@en-marche.fr">idees@en-marche.fr</a>
                            <img
                                src={icnClose}
                                className="icn-close"
                                onClick={event => hide(event.target.parentNode)}
                                alt="close icon"
                            />
                        </div>
                    </div>
                </div>

                <div className="programmatic-foundation__right">

                        <div className="programmatic-foundation__ressources b__nudge--top-40">
                            <a className="thumbnail thumbnail--one" href="https://storage.googleapis.com/en-marche-fr/pole_idees/Municipales/recueil_municipales.pdf" target="_blank">
                                <div className="thumbnail__content">
                                    300 projets qui marchent : le recueil →
                                </div>
                            </a>
                            <a className="thumbnail thumbnail--two" href="https://storage.googleapis.com/en-marche-fr/pole_idees/Municipales/12_idees_emblematiques.pdf" target="_blank">
                                <div className="thumbnail__content">
                                    Les 12 idées emblématiques →
                                </div>
                            </a>
                        </div>

                        <div className="l__row l__row--h-stretch l__row--wrap">
                            <div className="programmatic-foundation__legend">
                                <span className="legend-item basic-measure">Mesure</span>
                                <span className="legend-item project">Projet illustratif</span>
                            </div>
                        </div>

                        {this.state.isLoading ?
                            <Loader title="Chargement..." wrapperClassName="text--body space--30-0 text--center"/> :

                            <div>
                                <Breadcrumbs isSearching={isSearching} onExitClick={this.handleSearchExit}/>

                                <Content
                                    isSearching={isSearching}
                                    filterIsLeading={this.state.filterIsLeading}
                                    filterText={this.state.filterText}
                                    filterCity={this.state.filterCity}
                                    approaches={this.initialApproaches}
                                />
                            </div>
                        }
                </div>

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

    scrollToMyRef() {
        setTimeout(() => {
            ReactDOM.findDOMNode(this).scrollIntoView({behavior: "smooth"});
        }, 200);
    }

    handleFilterTextChange(text) {
        this.setState({filterText: text});

        this.scrollToMyRef();
    }

    handleFilterCityChange(text) {
        this.setState({filterCity: text});

        this.scrollToMyRef();
    }

    handleLeadingMeasuresChange(value) {
        this.setState({filterIsLeading: value});

        this.scrollToMyRef();
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
        })).sort((a, b) => a.localeCompare(b)).sort(function(a,b) {
            var importantResults = {
                'Petite commune': 1,
                'Ville moyenne': 2,
                'Métropole': 3,
                'Autre': 4,
            };

            var importantA = importantResults[a],
                importantB = importantResults[b],
                ret;

            if (importantA && !importantB) {ret = -1}
            else if (importantB && !importantA) {ret = 1}
            else if (importantA && importantB) {ret = importantA - importantB}
            else {ret = 0}

            return(ret);
        });
    }

    isSearching() {
        return !!this.state.filterText || !!this.state.filterCity;
    }

    handleSearchExit(event) {
        event.preventDefault();

        this.setState({
            filterText: '',
            filterCity: '',
            filterIsLeading: false,
        })
    }
}

ProgrammaticFoundation.propTypes = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
};
