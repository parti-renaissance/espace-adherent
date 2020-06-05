import React from 'react';
import PropTypes from 'prop-types';
import TextArea from '../../../TextArea';
import Select from '../../../Select';

class FirstForm extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            inputs: { ...this.props.defaultValues },
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
            <form className="first-form" onSubmit={this.handleSubmit}>
                <div className="first-form__section">
                    <h2 className="first-form__section__title">C'est presque fini !</h2>
                    <p className="first-form__section__subtitle">
            Nous avons besoin de quelques renseignements avant de publier votre proposition !
                    </p>
                </div>
                <div className="first-form__section">
                    <label className="first-form__section__label">Donnez aux marcheurs l'envie de lire votre proposition !</label>
                    <TextArea
                        maxLength={130}
                        placeholder="Décrivez en quelques mots le cœur de votre proposition"
                        error={this.state.errors.description}
                        onChange={value => this.handleChange('description', value)}
                        value={this.state.inputs.description}
                    />
                </div>
                <div className="first-form__section">
                    <label className="first-form__section__label">Thématique</label>
                    <Select
                        options={this.props.themeOptions}
                        placeholder="Choisissez la(es) thématique(s) de votre proposition"
                        subtitle="Ajoutez plusieurs thématiques si besoin, dans l'ordre de leur importance"
                        isMulti={true}
                        error={this.state.errors.theme}
                        onSelected={value => this.handleChange('theme', value)}
                        defaultValue={this.state.inputs.theme.length ? this.state.inputs.theme : undefined}
                        maxOptionsSelected={5}
                        maxOptionsLabel="thématique"
                    />
                </div>
                <div className="first-form__section">
                    <label className="first-form__section__label">À quel échelon se situe votre proposition ?</label>
                    <Select
                        options={this.props.localityOptions}
                        placeholder="Choisissez l'échelon de votre proposition"
                        subtitle={() => (
                            <p>
                Pour un projet local non généralisable, rapprochez-vous de votre référent via la rubrique contact du{' '}
                                <a className="link" href="https://dpt.en-marche.fr/" target="_blank" rel="noopener noreferrer">
                  site de votre territoire
                                </a>
                            </p>
                        )}
                        error={this.state.errors.locality}
                        onSelected={value => this.handleChange('locality', value)}
                        defaultValue={this.state.inputs.locality.length ? this.state.inputs.locality : undefined}
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
    defaultValues: { description: '', theme: [], locality: [] },
};

FirstForm.propTypes = {
    defaultValues: PropTypes.shape({
        description: PropTypes.string,
        theme: PropTypes.array,
        locality: PropTypes.array,
    }),
    themeOptions: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.number.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    localityOptions: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.number.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    onSubmit: PropTypes.func.isRequired,
};

export default FirstForm;
