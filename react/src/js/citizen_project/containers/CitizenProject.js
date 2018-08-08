import React, { Component } from 'react';
import TurnkeyProjectDetail from './../components/TurnkeyProjectDetail';

class CitizenProject extends Component {
    render() {
        return (
            <div className="citizen__wrapper">
                <h2 className="">Déjà 500 projets citoyens lancés !</h2>
                <p>
                    Les projets citoyens sont des actions locales qui visent à améliorer concrètement le quotidien{' '}
                    <br />
                    des habitants dans son quartier, son village, en réunissant la force et les compétences <br />
                    de tous ceux qui veulent agir.
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
                <a href="#" className="simple--link">
                    En savoir plus sur la Charte des Projets Citoyens
                </a>

                <h3>Découvrez quelques projets prêts à lancer !</h3>

                <TurnkeyProjectDetail
                    border="yellow"
                    video_id="DvZaHp0IfNo"
                    title="Des métiers pour demain"
                    subtitle="Inspirer nos jeunes pour les aider à s’orienter"
                    description="Aider les collégiens à mieux appréhender les métiers, en organisant des “speed-datings” ludiques avec des professionnels pour casser les idées reçues et en leur permettant de trouver un stage de découverte hors de leur réseau familial."
                    cta_content="Voir tous les projets prêts à lancer"
                    cta_border="yellow"
                />
            </div>
        );
    }
}

export default CitizenProject;
