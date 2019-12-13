import React, {PropTypes} from 'react';
import _ from 'lodash';
import ReqwestApiClient from '../../services/api/ReqwestApiClient';
import Content from './Content';
import SearchBar from './SearchBar';
import icnClose from './../../../web/images/icons/icn_close.svg';
import logoPQM from './../../../web/images/projets-qui-marchent-logo-horizontal.svg';
import Breadcrumbs from './Breadcrumbs';
import ToggleLeadingMeasures from './ToggleLeadingMeasures';
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

                        <div className="text--body b__nudge--top"><strong>Filtrer</strong></div>

                        <ToggleLeadingMeasures
                            onToggleChange={this.handleLeadingMeasuresChange}
                            value={this.state.filterIsLeading}
                        />

                        <SearchBar
                            filterText={this.state.filterText}
                            filterCity={this.state.filterCity}
                            filterCityChoices={this.extractAllCities()}
                            onFilterTextChange={this.handleFilterTextChange}
                            onFilterCityChange={this.handleFilterCityChange}
                        />

                        <div className="programmatic-foundation__contact">
                            Vous souhaitez partager un projet inspirant ou suggérer une mesure nouvelle ?
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

                        <div className="l__row l__row--h-stretch l__row--wrap">
                            <div className="programmatic-foundation__legend">
                                <span className="legend-item leading-measure">Mesure phare</span>
                                <span className="legend-item basic-measure">Mesure</span>
                                <span className="legend-item project">Projet inspirant</span>
                            </div>
                        </div>

                        {/*<a href="#" className="text--body text--blue--dark link--no-decor text--bold">Télécharger les mesures phares</a>*/}

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
        })).sort((a, b) => a.localeCompare(b));
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
