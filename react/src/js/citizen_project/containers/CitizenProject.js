import React, { Component } from 'react';
import { connect } from 'react-redux';

import { getCitizenProjects } from '../actions/citizen-projects';
import { getPinned } from '../actions/turnkey-projects';
import TurnkeyProjectDetail from './../components/TurnkeyProjectDetail';

class CitizenProject extends Component {
    componentDidMount() {
        this.props.dispatch(getCitizenProjects());
        this.props.dispatch(getPinned());
    }

    render() {
        const { count, pinned: { /* loading, */ project } } = this.props;
        return (
            <div className="citizen__wrapper">
                <h2 className="">Déjà {count} projets citoyens lancés !</h2>
                <p>
                    Les projets citoyens sont des actions locales qui visent à améliorer concrètement le quotidien{' '}
                    <br />
                    des habitants dans son quartier, son village, en réunissant la force et les compétences <br />
                    de tous ceux qui veulent agir. <br />
                    Vous avez donc la possibilité d'en lancer ou d'en rejoindre un !
                </p>
                <h3>Un projet citoyen c'est quoi ?</h3>
                <div className="citizen__helplist">
                    <div>
                        <span className="number">1</span>
                        <p>
                            Une initiative locale <br /> d'un collectif de citoyens
                        </p>
                    </div>
                    <div>
                        <span className="number">2</span>
                        <p>
                            Une action concrète <br /> au service des habitants, <br /> en lien avec les structures
                            existantes
                        </p>
                    </div>
                    <div>
                        <span className="number">3</span>
                        <p>
                            Un engagement bénévole <br /> ouvert à tous !
                        </p>
                    </div>
                </div>
                <p />
                <a href="www.google.fr" className="simple--link">
                    En savoir plus sur la Charte des Projets Citoyens
                </a>

                <h3>Découvrez quelques projets prêts à lancer !</h3>

                {project &&
                <TurnkeyProjectDetail
                    border="yellow"
                    video_id={project.video_id}
                    title={project.title}
                    subtitle={project.subtitle}
                    description={project.description}
                    cta_content="Voir tous les projets faciles à lancer"
                    cta_border="yellow"
                />
                }
            </div>
        );
    }
}

const mapStateToProps = state => ({
    count: state.citizen_project.citizen.count,
    pinned: state.citizen_project.turnkey.pinned,
});

export default connect(mapStateToProps)(CitizenProject);
