import React from 'react';
import PropTypes from 'prop-types';
import icn_state_fail from '../../../img/icn_state_fail.svg';

class FailSignal extends React.PureComponent {
    render() {
        return (
            <div className="error-modal">
                <img src={icn_state_fail} alt="Erreur" />
                <h3 className="error-modal__title">Oups</h3>
                <p className="error-modal__subtitle">
          Quelque chose s'est mal passé. Si le problème persiste, vérifiez que vous possédez une version à jour de votre
          navigateur{' '}
                    <a href="https://browsehappy.com/?locale=fr_FR" target="_blank" rel="noopener noreferrer">
            ici
                    </a>
                </p>
                <button className="button--secondary" onClick={() => this.props.submitAgain()}>
          Réessayer
                </button>
            </div>
        );
    }
}

FailSignal.propTypes = {
    submitAgain: PropTypes.func.isRequired,
};

export default FailSignal;
