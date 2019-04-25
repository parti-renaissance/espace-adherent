import React, { Component } from 'react';
import { connect } from 'react-redux';
import Select from 'react-select';

import {
    getCitizenProjects,
    getCategories,
    getCitiesAndCountries,
    setFilteredItem,
    loadMore,
} from '../actions/citizen-projects';

import CitizenProjectItem from './../components/CitizenProjectItem';

const NoProjects = () => (
    <div>
        <p className="citizen__project__grid--empty-content">
            Il n'y a pas de projet citoyen répondant à votre recherche ...
        </p>
        <iframe
            src="https://giphy.com/embed/9Y5BbDSkSTiY8"
            title="No result Found"
            width="480"
            height="336"
            frameBorder="0"
            className="giphy-embed"
            allowFullScreen
        />
    </div>
);

class CitizenProjectSearch extends Component {
    filterCategory = (selectedOption) => {
        this.props.dispatch(setFilteredItem({ category: selectedOption, page: 1 }));
    };

    filterCity = (selectedOption) => {
        this.props.dispatch(setFilteredItem({ city: selectedOption, page: 1 }));
    };

    filterByKeyword = (e) => {
        this.props.dispatch(setFilteredItem({ name: e.target.value, page: 1 }));
    };

    loadMore = () => this.props.dispatch(loadMore({ ...this.props.filter, page: this.props.filter.page + 1 }));

    componentDidMount() {
        window.scrollTo(0, 0);
        if (!this.props.projects.length) {
            this.props.dispatch(getCitizenProjects());
        }
        this.props.dispatch(getCategories());
        this.props.dispatch(getCitiesAndCountries());
    }

    componentDidUpdate({ filter: prevFilter }) {
        const {
            props: { filter: nextFilter },
        } = this;

        const hasDiffs = Object.keys(nextFilter)
            .filter(key => 'filterPending' !== key)
            .filter(key => 'page' !== key)
            .reduce((vals, key) => {
                vals.push([prevFilter[key], nextFilter[key]]);
                return vals;
            }, [])
            .some(([prev, next]) => prev !== next);

        if (hasDiffs) {
            this.props.dispatch(getCitizenProjects(nextFilter));
        }
    }

    render() {
        const { projects, filter, locales, categories, moreItems, loadingMore } = this.props;
        return (
            <div className="citizen__wrapper citizen__search">
                <h1 className="">Explorer tous les projets</h1>
                <p className="citizen__blurb">
                    Découvrez tous les projets citoyens déjà lancés !
                    <br />Rejoignez-en un près de chez vous ou créez le vôtre !
                </p>
                <div className="citizen__select__content">
                    <input
                        className="citizen__keyword-filter"
                        type="text"
                        value={filter.name}
                        onChange={this.filterByKeyword}
                        placeholder="Rechercher un projet par mot-clé"
                    />
                    <Select
                        value={filter.category}
                        onChange={this.filterCategory}
                        options={categories.map(cat => ({
                            label: cat,
                            value: cat,
                        }))}
                        placeholder="Filtrer par thème de projet"
                        simpleValue
                        noResultsText=" 🙈 Il n'y a pas de thème correspondant"
                    />
                    <Select
                        value={filter.city}
                        inputValue={filter.city}
                        onChange={this.filterCity}
                        onInputChange={this.filterCity}
                        options={locales.cities.map(city => ({
                            label: city,
                            value: city,
                        }))}
                        placeholder="Entrer le nom d'une ville"
                        onBlurResetsInput={false}
                        onCloseResetsInput={false}
                        onSelectResetsInput={false}
                        noResultsText={false}
                        simpleValue
                    />
                </div>
                <div className={`citizen__project__grid${0 === projects.length ? '--empty' : ''}`}>
                    {projects.length ? (
                        projects.map((project, i) => (
                            <CitizenProjectItem
                                key={i}
                                thumbnail={project.thumbnail}
                                title={project.name}
                                subtitle={project.subtitle}
                                author={project.author}
                                localisation={project.city}
                                url={project.url}
                                district={project.district}

                            />
                        ))
                    ) : (
                        <NoProjects />
                    )}
                </div>

                {moreItems && (
                    <button className="citizen__project__grid--more" disabled={loadingMore} onClick={this.loadMore}>
                        {loadingMore ? 'Chargement...' : 'Afficher plus'}
                    </button>
                )}

                {canCreateProject && (
                    <div className="citizen__wrapper__footer">
                        <a href="/espace-adherent/creer-mon-projet-citoyen" target="_blank" rel="nofollow noopener">
                            <span role="img" aria-label="emoji">
                                🚀
                            </span>{' '}
                            Une idée de projet ? Lancez-vous ! &rarr;
                        </a>
                    </div>
                )}
            </div>
        );
    }
}

const mapStateToProps = state => ({
    projects: state.citizen.projects,
    moreItems: state.citizen.moreItems,
    loadingMore: state.citizen.loadingMore,
    filter: state.citizen.filter,
    locales: state.locales,
    categories: state.categories.categories,
});

export default connect(mapStateToProps)(CitizenProjectSearch);
