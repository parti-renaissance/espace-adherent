import React, { Component } from 'react';
import Select from 'react-select';

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
    render() {
        const { selectedOption } = this.state;
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
                    <CitizenProjectItem
                        thumbnail="https://boygeniusreport.files.wordpress.com/2017/05/water.jpg"
                        title="Le coin du numérique"
                        subtitle="Rendre le numérique accessible à tous"
                        author="Guillaume D."
                        localisation="Paris 16"
                    />
                    <CitizenProjectItem
                        thumbnail="https://boygeniusreport.files.wordpress.com/2017/05/water.jpg"
                        title="Le coin du numérique"
                        subtitle="Rendre le numérique accessible à tous"
                        author="Guillaume D."
                        localisation="Paris 16"
                    />
                    <CitizenProjectItem
                        thumbnail="https://boygeniusreport.files.wordpress.com/2017/05/water.jpg"
                        title="Le coin du numérique"
                        subtitle="Rendre le numérique accessible à tous"
                        author="Guillaume D."
                        localisation="Paris 16"
                    />
                    <CitizenProjectItem
                        thumbnail="https://boygeniusreport.files.wordpress.com/2017/05/water.jpg"
                        title="Le coin du numérique"
                        subtitle="Rendre le numérique accessible à tous"
                        author="Guillaume D."
                        localisation="Paris 16"
                    />
                    <CitizenProjectItem
                        thumbnail="https://boygeniusreport.files.wordpress.com/2017/05/water.jpg"
                        title="Le coin du numérique"
                        subtitle="Rendre le numérique accessible à tous"
                        author="Guillaume D."
                        localisation="Paris 16"
                    />
                    <CitizenProjectItem
                        thumbnail="https://boygeniusreport.files.wordpress.com/2017/05/water.jpg"
                        title="Le coin du numérique"
                        subtitle="Rendre le numérique accessible à tous"
                        author="Guillaume D."
                        localisation="Paris 16"
                    />
                    <CitizenProjectItem
                        thumbnail="https://boygeniusreport.files.wordpress.com/2017/05/water.jpg"
                        title="Le coin du numérique"
                        subtitle="Rendre le numérique accessible à tous"
                        author="Guillaume D."
                        localisation="Paris 16"
                    />
                    <CitizenProjectItem
                        thumbnail="https://boygeniusreport.files.wordpress.com/2017/05/water.jpg"
                        title="Le coin du numérique"
                        subtitle="Rendre le numérique accessible à tous"
                        author="Guillaume D."
                        localisation="Paris 16"
                    />
                    <CitizenProjectItem
                        thumbnail="https://boygeniusreport.files.wordpress.com/2017/05/water.jpg"
                        title="Le coin du numérique"
                        subtitle="Rendre le numérique accessible à tous"
                        author="Guillaume D."
                        localisation="Paris 16"
                    />
                    <CitizenProjectItem
                        thumbnail="https://boygeniusreport.files.wordpress.com/2017/05/water.jpg"
                        title="Le coin du numérique"
                        subtitle="Rendre le numérique accessible à tous"
                        author="Guillaume D."
                        localisation="Paris 16"
                    />
                    <CitizenProjectItem
                        thumbnail="https://boygeniusreport.files.wordpress.com/2017/05/water.jpg"
                        title="Le coin du numérique"
                        subtitle="Rendre le numérique accessible à tous"
                        author="Guillaume D."
                        localisation="Paris 16"
                    />
                    <CitizenProjectItem
                        thumbnail="https://boygeniusreport.files.wordpress.com/2017/05/water.jpg"
                        title="Le coin du numérique"
                        subtitle="Rendre le numérique accessible à tous"
                        author="Guillaume D."
                        localisation="Paris 16"
                    />
                </div>
            </div>
        );
    }
}

export default CitizenProjectSearch;
