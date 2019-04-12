import React from 'react';
import PropTypes from 'prop-types';
import Button from '../../Button';
import icn_checklist from '../../../img/icn_checklist-white.svg';

class ExtendPeriod extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isChecked: false,
        };
    }

    render() {
        return (
            <div className="extend-modal">
                <h2>Prolongation de la période de contribution</h2>
                <p>
                    Vous avez ici la possibilité de bénéficier de 10 jours supplémentaires de contributions de
                    marcheurs. Vous ne pourrez utiliser cette action que 2 fois !
                </p>
                <div className="extend-modal__section__mentions">
                    <label className="extend-modal__section__mentions__checkbox">
                        <input
                            className="extend-modal__section__mentions__checkbox__input"
                            type="checkbox"
                            checked={this.state.isChecked}
                            onChange={e => this.setState(prevState => ({ isChecked: !prevState.isChecked }))}
                        />
                        <span className="extend-modal__section__mentions__checkbox__checkmark">
                            <img src={icn_checklist} alt="Checklist" />
                        </span>
                    </label>
                    <p className="extend-modal__section__mentions__text legal">
                        Je confirme mon souhait de rouvrir ma proposition aux contributions
                    </p>
                </div>
                <br />
                <small>
                    Cette action n'entraînera aucune perte de votre contenu. Les marcheurs ne pourront plus voter dessus
                    pendant 10 jours mais vos votes actuels seront conservés !
                </small>

                {this.state.isChecked && (
                    <Button
                        onClick={e => this.props.onExtendPeriod()}
                        type="submit"
                        className="button button--primary extend-modal__button"
                        label="Confirmer"
                    />
                )}
            </div>
        );
    }
}
ExtendPeriod.propTypes = {
    onExtendPeriod: PropTypes.func.isRequired,
};

export default ExtendPeriod;
