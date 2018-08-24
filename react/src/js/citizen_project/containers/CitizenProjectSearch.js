import React, { Component } from 'react';
import { connect } from 'react-redux';
import Select from 'react-select';

import {
    getCitizenProjects,
    getCategories,
    getCitiesAndCountries,
} from '../actions/citizen-projects';
import { setFilteredItem, filterCitizenProjects } from '../actions/filter';
import CitizenProjectItem from './../components/CitizenProjectItem';

class CitizenProjectSearch extends Component {
    filterCategory = (selectedOption) => {
        this.props.dispatch(setFilteredItem({ category: selectedOption }));
    };

    filterCity = (selectedOption) => {
        this.props.dispatch(setFilteredItem({ city: selectedOption }));
    };

    filterByKeyword = (e) => {
        this.props.dispatch(setFilteredItem({ keyword: e.target.value }));
    }

    componentDidMount() {
        if (!this.props.projects.length) {
            this.props.dispatch(getCitizenProjects());
        }
        this.props.dispatch(getCategories());
        this.props.dispatch(getCitiesAndCountries());
    }

    componentDidUpdate({ filter: { keyword, category, city } }) {
        const { props: { filter } } = this;
        if (keyword !== filter.keyword || category !== filter.category || city !== filter.city) {
            this.props.dispatch(filterCitizenProjects({
                keyword: filter.keyword,
                category: filter.category,
                city: filter.city,
            }));
        }
    }

    render() {
        const { projects, filter, locales, categories } = this.props;
        return (
            <div className="citizen__wrapper">
                <h2 className="">Explorer tous les projets</h2>
                <p>
                    Découvrez tous les projets citoyens déjà lancés !<br /> Vous pouvez en rejoindre un près de chez
                    vous ou encore vous inspirer d'un projet existant pour lancer le vôtre !
                </p>
                <div className="citizen__select__content">
                    <input
                        type="text"
                        value={filter.keyword}
                        onChange={this.filterByKeyword}
                        placeholder="Rechercher un projet par mot-clé"
                    />
                    <Select
                        value={filter.category}
                        onChange={this.filterCategory}
                        options={categories.map(cat => ({label: cat, value: cat}))}
                        placeholder="Filtrer par thème de projet"
                        simpleValue
                    />
                    <Select
                        value={filter.city}
                        onChange={this.filterCity}
                        options={locales.cities.map(city => ({label: city, value: city}))}
                        placeholder="Filtrer une ville"
                        simpleValue
                    />
                </div>
                <div className="citizen__project__grid">
                    {projects.map((project, i) => <CitizenProjectItem
                        key={i}
                        thumbnail={project.thumbnail}
                        title={project.title}
                        subtitle={project.subtitle}
                        author={project.author}
                        localisation={project.city}
                    />
                    )}
                </div>
            </div>
        );
    }
}

const mapStateToProps = state => ({
    projects: state.citizen_project.citizen.projects,
    filter: state.citizen_project.filter,
    locales: state.citizen_project.locales,
    categories: state.citizen_project.categories.categories,
});

export default connect(mapStateToProps)(CitizenProjectSearch);
