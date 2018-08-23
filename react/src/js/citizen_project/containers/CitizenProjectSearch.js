import React, { Component } from 'react';
import { connect } from 'react-redux';
import Select from 'react-select';

import { getCitizenProjects } from '../actions/citizen-projects';
import CitizenProjectItem from './../components/CitizenProjectItem';

const options = [
    { value: 'chocolate', label: 'Chocolate' },
    { value: 'strawberry', label: 'Strawberry' },
    { value: 'vanilla', label: 'Vanilla' },
];

class CitizenProjectSearch extends Component {
    state = {
        selectedOption: null,
    };
    handleChange = (selectedOption) => {
        this.setState({ selectedOption });
        console.log('Option selected:', selectedOption);
    };

    componentDidMount() {
        if (!this.props.projects.length) {
            this.props.dispatch(getCitizenProjects());
        }
    }
    render() {
        const { selectedOption } = this.state;
        const { projects } = this.props;
        return (
            <div className="citizen__wrapper">
                <h2 className="">Explorer tous les projets</h2>
                <p>
                    Découvrez tous les projets citoyens déjà lancés !<br /> Vous pouvez en rejoindre un près de chez
                    vous ou encore vous inspirer d'un projet existant pour lancer le vôtre !
                </p>
                <div className="citizen__select__content">
                    <Select
                        value={selectedOption}
                        onChange={this.handleChange}
                        options={options}
                        placeholder="Rechercher un projet par mot-clé"
                    />
                    <Select
                        value={selectedOption}
                        onChange={this.handleChange}
                        options={options}
                        placeholder="Filtrer par thème de projet"
                    />
                    <Select
                        value={selectedOption}
                        onChange={this.handleChange}
                        options={options}
                        placeholder="Filtrer une ville"
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
});

export default connect(mapStateToProps)(CitizenProjectSearch);
