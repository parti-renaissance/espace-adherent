import React from 'react';
import PropTypes from 'prop-types';
import { Link } from 'react-router-dom';
import TextArea from '../../../TextArea';
import Select from '../../../Select';

class FirstForm extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            inputs: { ...this.props.initInputs },
            errors: {
                form: '',
                description: '',
                theme: '',
                locality: '',
            },
        };
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleErrors = this.handleErrors.bind(this);
        this.handleChange = this.handleChange.bind(this);
    }

    handleErrors() {
        let canSubmit = true;
        const verifErrors = Object.keys(this.state.inputs).reduce((acc, curr) => {
            if (!this.state.inputs[curr].length) {
                // TODO: modify error msg
                acc[curr] = 'Information manquante';
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
            <form class="first-form" onSubmit={this.handleSubmit}>
                <div class="first-form__section">
                    <h2 class="first-form__section__title">Soumettez votre note ici</h2>
                    <p class="first-form__section__subtitle">
						Une fois ce dernier formulaire rempli, votre note pourra être enrichie{' '}
                        <Link className="link" to="/atelier-des-idees/contribuer" target="_blank">
							ici
                        </Link>{' '}
						par des contributions d’adhérents pendant 3 semaines. Passé ce délai, elle sera affichée{' '}
                        <Link className="link" to="/atelier-des-idees/consulter" target="_blank">
							là
                        </Link>{' '}
						et pourra être soumise aux votes des adhérents.
                    </p>
                </div>
                <div class="first-form__section">
                    <label class="first-form__section__label">Description de l’idée</label>
                    <TextArea
                        maxLength={180}
                        placeholder="Décrivez votre idée (180 caractères max)"
                        error={this.state.errors.description}
                        onChange={value => this.handleChange('description', value)}
                        value={this.state.inputs.description}
                    />
                </div>
                <div class="first-form__section">
                    <label class="first-form__section__label">Thématique</label>
                    <Select
                        options={this.props.themeOptions}
                        placeholder="Choisissez la(es) thémathique(s) de votre note"
                        subtitle="Ajoutez plusieurs thémathiques si besoin, dans l'ordre de leur importance"
                        isMulti={true}
                        error={this.state.errors.theme}
                        onSelected={value => this.handleChange('theme', value)}
                    />
                </div>
                <div class="first-form__section">
                    <label class="first-form__section__label">Est-ce un projet national ou européen ?</label>
                    <Select
                        options={this.props.localityOptions}
                        placeholder="Choisissez l'échelle de votre note"
                        subtitle={() => (
                            <p>
								Pour un projet local, rapprochez-vous de votre référent via la rubrique contact du{' '}
                                {/* TODO: missing a link */}
                                <Link className="link" to="/atelier-des-idees">
									site de votre territoire
                                </Link>
                            </p>
                        )}
                        error={this.state.errors.locality}
                        onSelected={value => this.handleChange('locality', value)}
                    />
                </div>
                <button type="submit" className="first-form__button button--secondary">
					dernière étape →
                </button>
                {this.state.errors.form && <p className="first-form__error">{this.state.errors.form}</p>}
            </form>
        );
    }
}

FirstForm.defaultProps = {
    initInputs: { description: '', theme: [], locality: [] },
};

FirstForm.propTypes = {
    initInputs: PropTypes.shape({
        description: PropTypes.string,
        theme: PropTypes.array,
        locality: PropTypes.array,
    }),
    themeOptions: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.string.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    localityOptions: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.string.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    onSubmit: PropTypes.func.isRequired,
};

export default FirstForm;
