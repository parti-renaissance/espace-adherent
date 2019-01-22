import React from 'react';
import PropTypes from 'prop-types';
import Select from '../../../Select';
import Button from '../../../Button';
import icn_checklist from './../../../../img/icn_checklist-white.svg';

class SecondForm extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            inputs: {
                ...props.defaultValues,
            },
            errors: {
                form: '',
                author: '',
                legal: '',
            },
            isSubmitting: false,
        };
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleErrors = this.handleErrors.bind(this);
        this.handleChange = this.handleChange.bind(this);
        this.checkIfComittee = this.checkIfComittee.bind(this);
    }
    componentDidMount() {
        window.scrollTo(0, 0);
    }
    handleErrors() {
        let canSubmit = true;
        const verifErrors = Object.keys(this.state.inputs).reduce((acc, curr) => {
            // if attribute is in state.errors : input is required
            const isRequired = Object.keys(this.state.errors).includes(curr);
            // check if it's an array
            const isArrayEmpty = Array.isArray(this.state.inputs[curr]) && !this.state.inputs[curr].length;
            // check array is not empty and boolean is true
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
                canSubmit = true;
            }
            return acc;
        }, {});
        this.setState({ errors: verifErrors });
        return canSubmit;
    }

    handleSubmit(event) {
        event.preventDefault();
        if (this.handleErrors()) {
            this.setState({ isSubmitting: true });
            this.props.onSubmit(this.state.inputs);
        }
    }

    handleChange(input, value) {
        this.setState(
            prevState => ({
                ...prevState,
                inputs: { ...prevState.inputs, [input]: value },
            }),
            () => {
                if ('author' === input) {
                    this.checkIfComittee(value);
                }
                this.props.saveStateFormOnChange(this.state.inputs);
            }
        );
    }

    checkIfComittee(res) {
        if ('committee' === res[0].value) {
            this.setState(prevState => ({
                ...prevState,
                // add committee attribute to inputs
                inputs: { ...prevState.inputs, committee: [] },
                // committee is required
                errors: { ...prevState.errors, committee: '' },
            }));
        } else {
            // value === alone
            // remove committee atributes
            this.setState(prevState => ({
                ...prevState,
                errors: {
                    author: prevState.errors.author,
                    legal: prevState.errors.legal,
                },
                inputs: {
                    author: prevState.inputs.author,
                    difficulties: prevState.inputs.difficulties,
                    legal: prevState.inputs.legal,
                },
            }));
        }
    }

    render() {
        const showCommittee = 0 < this.state.inputs.author.length && 'committee' === this.state.inputs.author[0].value;
        return (
            <form className="second-form" onSubmit={this.handleSubmit}>
                <div className="second-form__section">
                    <label className="second-form__section__label">Avec qui proposez-vous cette idée ?</label>
                    <Select
                        options={this.props.authorOptions}
                        placeholder="Seul / Mon comité"
                        error={this.state.errors.author}
                        onSelected={value => this.handleChange('author', value)}
                        defaultValue={this.state.inputs.author.length ? this.state.inputs.author : undefined}
                    />
                </div>
                {showCommittee && (
                    <div className="second-form__section">
                        <label className="second-form__section__label">Nom de votre comité</label>
                        <Select
                            options={this.props.committeeOptions}
                            error={this.state.errors.committee}
                            onSelected={value => this.handleChange('committee', value)}
                            defaultValue={this.state.inputs.committee ? this.state.inputs.committee : undefined}
                        />
                    </div>
                )}
                <div className="second-form__section">
                    <label className="second-form__section__label">
                        Y a-t-il des parties sur lesquelles vous souhaitez recevoir les suggestions des autres adhérents
                        ?<span className="second-form__section__label__optional"> (Optionnel)</span>
                    </label>
                    <Select
                        options={this.props.difficultiesOptions}
                        placeholder="Droit / Budget / etc."
                        isMulti={true}
                        onSelected={value => this.handleChange('difficulties', value)}
                        defaultValue={
                            this.state.inputs.difficulties.length ? this.state.inputs.difficulties : undefined
                        }
                    />
                </div>
                <div className="second-form__section">
                    <div className="second-form__section__mentions">
                        <label className="second-form__section__mentions__checkbox">
                            <input
                                className="second-form__section__mentions__checkbox__input"
                                type="checkbox"
                                checked={this.state.inputs.legal}
                                onChange={event => this.handleChange('legal', event.target.checked)}
                            />
                            <span className="second-form__section__mentions__checkbox__checkmark">
                                <img src={icn_checklist} />
                            </span>
                        </label>
                        <p className="second-form__section__mentions__text">
                            J’accepte les{' '}
                            <a
                                className="second-form__section__mentions__text__link"
                                href="/atelier-des-idees/cgu"
                                target="_blank"
                            >
                                mentions légales
                            </a>
                        </p>
                    </div>
                    {this.state.errors.legal && (
                        <p className="second-form__section__mentions--error">{this.state.errors.legal}</p>
                    )}
                    <p className="second-form__section__text">
                        Les données recueillies sur ce formulaire sont traitées par La REM afin de gérer les
                        informations relatives aux adhérents de La REM et lui permettent d’utiliser vos données pour des
                        opérations de communications politiques. Les informations marquées d’un astérisque sont
                        obligatoires. L’absence de réponse dans ces champs ne permettra pas à La REM de traiter votre
                        demande. Conformément à la règlementation, vous disposez d’un droit d’opposition et d’un droit à
                        la limitation du traitement de données vous concernant, ainsi que d’un droit d’accès, de
                        rectification, de portabilité et d’effacement de vos données. Vous disposez également de la
                        faculté de donner des directives sur le sort de vos données après votre décès. Vous pouvez
                        exercer vos droits en nous adressant votre demande accompagnée d’une copie de votre pièce
                        d’identité à l’adresse postale ou électronique suivante : La République En Marche, 63 rue
                        Sainte-Anne, 75002 Paris, France et mes-donnees@en-marche.fr.
                    </p>
                </div>
                <Button
                    type="submit"
                    className="second-form__button button--primary"
                    label="Publier la proposition"
                    isLoading={this.state.isSubmitting}
                />
                {this.state.errors.form && <p className="second-form__error">{this.state.errors.form}</p>}
            </form>
        );
    }
}

SecondForm.defaultProps = {
    defaultValues: {
        author: [],
        difficulties: [],
        legal: false,
    },
};

SecondForm.propTypes = {
    defaultValues: PropTypes.shape({
        author: PropTypes.array,
        difficulties: PropTypes.array,
        legal: PropTypes.boolean,
    }),
    authorOptions: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.string.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    committeeOptions: PropTypes.arrayOf(
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
    saveStateFormOnChange: PropTypes.func.isRequired,
};

export default SecondForm;
