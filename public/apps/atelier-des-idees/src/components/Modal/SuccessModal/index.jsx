import React from 'react';
import PropTypes from 'prop-types';
import icn_state_success from '../../../img/icn_state_success.svg';

class SuccessModal extends React.PureComponent {
    render() {
        return (
            <div className="success-modal">
                <img className="success-modal__img" src={icn_state_success} />
                <h3 className="success-modal__title">Merci</h3>
                <p className="success-modal__text">{this.props.text}</p>
            </div>
        );
    }
}

SuccessModal.propTypes = {
    text: PropTypes.string.isRequired,
};

export default SuccessModal;
