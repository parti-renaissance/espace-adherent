import React from 'react';
import PropTypes from 'prop-types';
import { Link } from 'react-router-dom';
import icn_state_success from './../../../../../img/icn_state_success.svg';
import how_to_approve from './../../../../../img/how-to-approve.svg';

class SuccessForm extends React.PureComponent {
    componentDidMount() {
        window.scrollTo(0, 0);
    }
    render() {
        return (
            <div className="success-form">
                <img src={icn_state_success} />
                <h3 className="success-form__title">
					Merci ! Votre proposition a été publiée
                </h3>
                <p className="success-form__subtitle">
                  Pendant 10 jours, les adhérents pourront faire des commentaires afin de vous aider à enrichir et compléter votre proposition ! Vous pourrez {' '}
                  <span className="success-form__subtitle__approved">Approuver</span> leurs commentaires et intégrer leurs contributions !
                </p>
                <a
                    href={`/atelier-des-idees/proposition/${this.props.id}`}
                    className="success-form__button button--secondary"
                >
					Voir la page
                </a>
                <img src={how_to_approve} />
            </div>
        );
    }
}

SuccessForm.propTypes = {
    id: PropTypes.string.isRequired,
};

export default SuccessForm;
