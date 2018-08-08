import React, { Component } from 'react';

import TurnkeyProjectDetail from './../components/TurnkeyProjectDetail';
import TurnkeyProjectListItem from './../components/TurnkeyProjectListItem';

class CitizenProjectTurnKey extends Component {
    render() {
        return (
            <div className="citizen__wrapper">
                <h2>Les projets citoyens prêts à lancer</h2>
                <p>
                    Un projet clé en main, c'est l'opportunité de disposer de conseils adaptés à chaque <br /> étape de
                    votre projet et d'outils sur-mesure pour vous aider pas à pas dans votre action.
                </p>

                <div className="turnkey__project__content">
                    <div className="turnkey__project__detail">
                        <TurnkeyProjectDetail
                            border="light"
                            video_id="DvZaHp0IfNo"
                            title="Des métiers pour demain"
                            subtitle="Inspirer nos jeunes pour les aider à s’orienter"
                            description="Aider les collégiens à mieux appréhender les métiers, en organisant des “speed-datings” ludiques avec des professionnels pour casser les idées reçues et en leur permettant de trouver un stage de découverte hors de leur réseau familial."
                            cta_content="Je lance ce projet clé en main"
                            cta_border="green"
                        />
                    </div>
                    <div className="turnkey__project__list">
                        <TurnkeyProjectListItem
                            category="Emploi"
                            title="Des métiers pour demain"
                            subtitle="Inspirer nos jeunes pour les aider à s'orienter"
                        />
                        <TurnkeyProjectListItem
                            category="Emploi"
                            title="Des métiers pour demain"
                            subtitle="Inspirer nos jeunes pour les aider à s'orienter"
                        />
                        <TurnkeyProjectListItem
                            category="Emploi"
                            title="Des métiers pour demain"
                            subtitle="Inspirer nos jeunes pour les aider à s'orienter"
                        />
                        <TurnkeyProjectListItem
                            category="Emploi"
                            title="Des métiers pour demain"
                            subtitle="Inspirer nos jeunes pour les aider à s'orienter"
                        />
                        <TurnkeyProjectListItem
                            category="Emploi"
                            title="Des métiers pour demain"
                            subtitle="Inspirer nos jeunes pour les aider à s'orienter"
                        />
                        <TurnkeyProjectListItem
                            category="Emploi"
                            title="Des métiers pour demain"
                            subtitle="Inspirer nos jeunes pour les aider à s'orienter"
                        />
                        <TurnkeyProjectListItem
                            category="Emploi"
                            title="Des métiers pour demain"
                            subtitle="Inspirer nos jeunes pour les aider à s'orienter"
                        />
                        <TurnkeyProjectListItem
                            category="Emploi"
                            title="Des métiers pour demain"
                            subtitle="Inspirer nos jeunes pour les aider à s'orienter"
                        />
                        <TurnkeyProjectListItem
                            category="Emploi"
                            title="Des métiers pour demain"
                            subtitle="Inspirer nos jeunes pour les aider à s'orienter"
                        />
                        <TurnkeyProjectListItem
                            category="Emploi"
                            title="Des métiers pour demain"
                            subtitle="Inspirer nos jeunes pour les aider à s'orienter"
                        />
                    </div>
                </div>
            </div>
        );
    }
}

export default CitizenProjectTurnKey;
