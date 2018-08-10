import React, { Component } from 'react';

import TurnkeyProjectDetail from './../components/TurnkeyProjectDetail';
import TurnkeyProjectListItem from './../components/TurnkeyProjectListItem';

class CitizenProjectTurnKey extends Component {
    render() {
        return (
            <div className="citizen__wrapper">
                <h2>Les projets citoyens faciles à lancer</h2>
                <p>
                    Voici les projets faciles à lancer qui ont déjà été lancés avec succès dans de nombreuses villes de
                    France. <br />Choisissez-en un et lancez le facilement près de chez vous !
                </p>

                <div className="turnkey__project__content">
                    <div className="turnkey__project__detail">
                        <TurnkeyProjectDetail
                            border="light"
                            video_id="DvZaHp0IfNo"
                            title="Des métiers pour demain"
                            subtitle="Inspirer nos jeunes pour les aider à s’orienter"
                            description="Aider les collégiens à mieux appréhender les métiers, en organisant des “speed-datings” ludiques avec des professionnels pour casser les idées reçues et en leur permettant de trouver un stage de découverte hors de leur réseau familial."
                            cta_content="Je soumets une demande de création"
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
