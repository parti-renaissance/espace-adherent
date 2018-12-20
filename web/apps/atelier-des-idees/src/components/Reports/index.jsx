import React from 'react';

class Reports extends React.PureComponent {
    render() {
        return (
            <div className="reports">
                <div className="reports__first-section">
                    <h3 className="reports__first-section__title">
						Nous n’avons pas attendu l’Atelier des Idées pour vous consulter
                    </h3>
                    <p className="reports__first-section__text">
						Consultez les rapports de consultations terminées
                    </p>
                    <button className="reports__first-section__button button--primary">
						Je lis les rapports
                    </button>
                </div>
                <div className="reports__second-section">
                    {/* TODO: replace by image */}
                    <div className="form" />
                </div>
            </div>
        );
    }
}

export default Reports;
