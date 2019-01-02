import React from 'react';
import PropTypes from 'prop-types';

class FailForm extends React.PureComponent {
    render() {
        return (
            <div className="fail-form">
                <img src="/assets/img/icn_state_fail.svg" />
                <h3 className="fail-form__title">OUPS</h3>
                <p className="fail-form__subtitle">Quelque chose s'est mal passé</p>
                <button
                    className="button--secondary"
                    onClick={() => this.props.submitAgain()}
                >
					Réessayer
                </button>
            </div>
        );
    }
}

FailForm.propTypes = {
    submitAgain: PropTypes.func.isRequired,
};

export default FailForm;
