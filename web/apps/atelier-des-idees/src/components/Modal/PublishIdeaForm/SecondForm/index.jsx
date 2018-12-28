import React from 'react';
import PropTypes from 'prop-types';
import { Link } from 'react-router-dom';
import Select from '../../../Select';

class SecondForm extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            inputs: {
                author: [],
                difficulties: [],
                legal: false,
            },
            errors: {
                form: '',
                author: '',
                legal: '',
            },
        };
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleErrors = this.handleErrors.bind(this);
        this.handleChange = this.handleChange.bind(this);
    }

    handleErrors() {
        let canSubmit = true;
        const verifErrors = Object.keys(this.state.inputs).reduce((acc, curr) => {
            const isRequired = Object.keys(this.state.errors).includes(curr);
            const isArrayEmpty =
				Array.isArray(this.state.inputs[curr]) &&
				!this.state.inputs[curr].length;
            if (isRequired && (!this.state.inputs[curr] || isArrayEmpty)) {
                // TODO: modify error msg
                acc[curr] = 'Information manquante';
                if (!acc.form) {
                    acc.form = 'Certaines informations sont manquantes ou erronées';
                }
                canSubmit = false;
            } else {
                acc[curr] = '';
                acc.form = '';
            }
            return acc;
        }, {});
        this.setState({ errors: verifErrors });
        return canSubmit;
    }

    handleSubmit(event) {
        event.preventDefault();
        if (this.handleErrors()) this.props.onSubmit(this.state.inputs);
    }

    handleChange(input, value) {
        this.setState(prevState => ({
            ...prevState,
            inputs: { ...prevState.inputs, [input]: value },
        }));
    }

    render() {
        return (
            <form class="second-form" onSubmit={this.handleSubmit}>
                <div class="second-form__section">
                    <label class="second-form__section__label">
						Avec qui avez-vous rédigé cette note ?
                    </label>
                    <Select
                        options={this.props.authorOptions}
                        placeholder="Seul / Mon comité"
                        error={this.state.errors.author}
                        onSelected={value => this.handleChange('author', value)}
                    />
                </div>
                <div class="second-form__section">
                    <label class="second-form__section__label">
						Y-a-t-il une partie qui vous a semblé difficile à remplir ?
                        <span class="second-form__section__label__optional">
                            {' '}
							(Optionnel)
                        </span>
                    </label>
                    <Select
                        options={this.props.difficultiesOptions}
                        placeholder="Juridique / Finance / etc."
                        isMulti={true}
                        onSelected={value => this.handleChange('difficulties', value)}
                    />
                </div>
                <div class="second-form__section">
                    <div className="second-form__section__mentions">
                        <label className="second-form__section__mentions__checkbox">
                            <input
                                className="second-form__section__mentions__checkbox__input"
                                type="checkbox"
                                checked={this.state.legal}
                                onChange={event =>
                                    this.handleChange('legal', event.target.checked)
                                }
                            />
                            <span className="second-form__section__mentions__checkbox__checkmark">
                                <img src="/assets/img/icn_checklist-white.svg" />
                            </span>
                        </label>
                        <p className="second-form__section__mentions__text">
                            {/* TODO: Missing link */}
							J’accepte les{' '}
                            <Link
                                to="/atelier-des-idees"
                                className="second-form__section__mentions__text__link"
                                target="_blank"
                            >
								mentions légales
                            </Link>
                        </p>
                    </div>
                    {this.state.errors.legal && (
                        <p className="second-form__section__mentions--error">
                            {this.state.errors.legal}
                        </p>
                    )}
                    <p className="second-form__section__text">
						Les données recueillies sur ce formulaire sont traitées par La REM
						afin de gérer les informations relatives aux adhérents de La REM et
						lui permettent d’utiliser vos données pour des opérations de
						communications politiques. Les informations marquées d’un astérisque
						sont obligatoires. L’absence de réponse dans ces champs ne permettra
						pas à La REM de traiter votre demande. Conformément à la
						règlementation, vous disposez d’un droit d’opposition et d’un droit
						à la limitation du traitement de données vous concernant, ainsi que
						d’un droit d’accès, de rectification, de portabilité et d’effacement
						de vos données. Vous disposez également de la faculté de donner des
						directives sur le sort de vos données après votre décès. Vous pouvez
						exercer vos droits en nous adressant votre demande accompagnée d’une
						copie de votre pièce d’identité à l’adresse postale ou électronique
						suivante : La République En Marche, 63 rue Sainte-Anne, 75002 Paris,
						France et mes-donnees@en-marche.fr.
                    </p>
                </div>
                <button type="submit" className="second-form__button button--primary">
					publier la note
                </button>
                {this.state.errors.form && (
                    <p className="second-form__error">{this.state.errors.form}</p>
                )}
            </form>
        );
    }
}

SecondForm.propTypes = {
    authorptions: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.string.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    difficultiesOptions: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.string.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    onSubmit: PropTypes.func.isRequired,
};

export default SecondForm;
