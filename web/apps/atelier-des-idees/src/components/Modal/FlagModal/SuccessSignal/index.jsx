import React from 'react';

class SuccessSignal extends React.PureComponent {
    render() {
        return (
            <div className="success-signal">
                <img
                    className="success-signal__img"
                    src="/assets/img/icn_state_success.svg"
                />
                <h3 className="success-signal__title">Merci</h3>
                <p className="success-signal__text">
					Votre signalement a bien été pris en compte.
                </p>
                <p className="success-signal__text">
					L'entité signalée va disparaître temporairement du site en attendant
					d'être analysée par les équipes de LaREM.
                </p>
            </div>
        );
    }
}

export default SuccessSignal;
