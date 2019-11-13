import React from 'react';

export default class Legend extends React.Component {
    render() {
        return (
            <div className="programmatic-foundation__legend">
                <span className="legend-title">Légende :</span>
                <span className="legend-item basic-measure">Mesure</span>
                <span className="legend-item leading-measure">Mesure phare</span>
                <span className="legend-item project">Projet illustratif</span>
            </div>
        );
    }
}
