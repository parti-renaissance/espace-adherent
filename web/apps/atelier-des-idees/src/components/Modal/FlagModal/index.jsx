import React from 'react';
import PropTypes from 'prop-types';
import Select from '../../Select';
import TextArea from '../../TextArea';

class FlagModal extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            inputs: {
                reasons: [],
            },
            errors: {
                reasons: '',
                form: '',
            },
        };
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleErrors = this.handleErrors.bind(this);
        this.handleChange = this.handleChange.bind(this);
        this.checkIfOther = this.checkIfOther.bind(this);
    }

    checkIfOther() {
        const check = this.state.inputs.reasons.filter(
            reason => 'other' === reason.value
        ).length;
        if (check) {
            this.setState(prevState => ({
                ...prevState,
                // add comment attribute to inputs
                inputs: { ...prevState.inputs, comment: '' },
                // comment is required
                errors: { ...prevState.errors, comment: '' },
            }));
        } else {
            this.setState(prevState => ({
                ...prevState,
                errors: {
                    reasons: prevState.errors.reasons,
                },
                inputs: {
                    reasons: prevState.inputs.reasons,
                },
            }));
        }
    }

    handleChange(input, value) {
        this.setState(
            prevState => ({
                ...prevState,
                inputs: { ...prevState.inputs, [input]: value },
            }),
            () => {
                if ('reasons' === input) this.checkIfOther();
            }
        );
    }

    handleSubmit(event) {
        event.preventDefault();
        if (this.handleErrors()) this.props.onSubmit(this.state.inputs);
    }

    handleErrors() {
        let canSubmit = true;
        const verifErrors = Object.keys(this.state.inputs).reduce((acc, curr) => {
            if (!this.state.inputs[curr].length) {
                switch (curr) {
                case 'reasons':
                    acc[curr] =
							'Afin de valider votre signalement, veuillez sélectionner au moins une raison.';
                    break;
                case 'comment':
                    acc[curr] = 'Merci de renseigner la raison de votre signalement.';
                    break;
                default:
                    acc[curr] = 'Information manquante';
                }
                if (!acc.form) {
                    acc.form = 'Certaines informations sont manquantes ou erronées';
                }
                canSubmit = false;
            } else {
                acc[curr] = '';
            }
            return acc;
        }, {});
        this.setState({ errors: verifErrors });
        return canSubmit;
    }

    render() {
        return (
            <form className="flag-modal" onSubmit={this.handleSubmit}>
                <h2 className="flag-modal__title">Signaler un abus</h2>
                <p className="flag-modal__subtitle">
					Veuillez signaler tout élément qui pourrait contrevenir aux Conditions
					d'utilisation ou à la Charte des bonnes pratiques de La République En
					Marche.
                </p>
                <div className="flag-modal__reasons">
                    <label className="flag-modal__label">Raison du signalement </label>
                    <Select
                        options={this.props.reasons}
                        isMulti={true}
                        placholder="Discours incitant à la haine"
                        onSelected={value => this.handleChange('reasons', value)}
                        error={this.state.errors.reasons}
                    />
                </div>
                {this.state.inputs.reasons.some(reason => 'other' === reason.value) && (
                    <div className="flag-modal__comment">
                        <label className="flag-modal__label">Autre raison </label>
                        <TextArea
                            value={this.state.inputs.comment}
                            error={this.state.errors.comment}
                            placeholder="Merci d'expliquer pourquoi vous signalez ce commentaire ou cette idée à La République En Marche et merci d'indiquer toute information qui nous aiderait à l'évaluer"
                            onChange={value => this.handleChange('comment', value)}
                        />
                    </div>
                )}

                <button
                    type="submit"
                    className="button button--primary flag-modal__button"
                >
					ENVOYER
                </button>
                {this.state.errors.form && (
                    <p className="flag-modal__error">{this.state.errors.form}</p>
                )}
                <p className="flag-modal__text">
					Les commentaies ou idées signalés sont passés en revue par les équipes
					de La République En Marche afin de déterminer s'ils violent nos
					Conditions d'utilisation ou notre Charte des bonnes pratiques
                </p>
            </form>
        );
    }
}

FlagModal.propTypes = {
    reasons: PropTypes.array.isRequired,
    onSubmit: PropTypes.func.isRequired,
};

export default FlagModal;
