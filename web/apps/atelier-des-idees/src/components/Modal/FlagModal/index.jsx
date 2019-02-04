import React from 'react';
import PropTypes from 'prop-types';
import Select from '../../Select';
import TextArea from '../../TextArea';
import SuccessSignal from './SuccessSignal';
import ErrorModal from '../ErrorModal';

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
        this.submitForm = this.submitForm.bind(this);
    }

    checkIfOther() {
        const check = this.state.inputs.reasons.filter(reason => 'other' === reason).length;
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
        if (this.handleErrors()) this.submitForm();
    }

    submitForm() {
        this.props.onSubmit(this.state.inputs);
    }

    handleErrors() {
        let canSubmit = true;
        const verifErrors = Object.keys(this.state.inputs).reduce((acc, curr) => {
            if (!this.state.inputs[curr].length) {
                switch (curr) {
                case 'reasons':
                    acc[curr] = 'Afin de valider votre signalement, veuillez sélectionner au moins une raison.';
                    break;
                case 'comment':
                    acc[curr] = 'Merci de renseigner la raison de votre signalement.';
                    break;
                default:
                    acc[curr] = 'Information manquante';
                }
                if (!acc.form) {
                    acc.form = 'Certaines informations sont manquantes';
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
            <React.Fragment>
                {!this.props.isSubmitSuccess && !this.props.isSubmitError && (
                    <form className="flag-modal" onSubmit={this.handleSubmit}>
                        <h2 className="flag-modal__title">Signaler un abus</h2>
                        <p className="flag-modal__text">
                            Conformément aux dispositions de l’article 6 la loi n°2004-575 du 21 juin 2004 pour la
                            confiance dans l’économie numérique, toute personne peut signaler tout message à caractère
                            litigieux dont elle aurait connaissance notamment ceux qui relèveraient des infractions
                            prévues aux articles 24 alinéas 5, 7 et 8 de la loi sur la liberté de la presse du 29
                            juillet 1881 et des articles 227-23, 227-24 et 421-2-5 du Code pénal tels que l’apologie des
                            crimes contre l’humanité, l’incitation à la haine raciale, à la violence et au terrorisme,
                            la pornographie enfantine, les atteintes à la dignité humaine etc.
                        </p>
                        <div className="flag-modal__reasons">
                            <label className="flag-modal__label">Raison du signalement </label>
                            <Select
                                options={this.props.reasons}
                                isMulti={false}
                                placeholder="Choisissez la raison de votre signalement"
                                onSelected={([{ value }]) => this.handleChange('reasons', [value])}
                                error={this.state.errors.reasons}
                            />
                        </div>
                        {this.state.inputs.reasons.some(reason => 'other' === reason) && (
                            <div className="flag-modal__comment">
                                <label className="flag-modal__label">Autre raison </label>
                                <TextArea
                                    value={this.state.inputs.comment}
                                    error={this.state.errors.comment}
                                    placeholder="Décrivez ici les propos litigieux que vous avez constatés ainsi que les motifs pour lesquels le contenu doit être retiré"
                                    onChange={value => this.handleChange('comment', value)}
                                    maxLength={500}
                                />
                            </div>
                        )}

                        <p className="flag-modal__text">
                            Nous vous rappelons que l’article 6.I.4 de la loi n°2004-575 du 21 juin 2004 précitée
                            dispose également que « le fait, pour toute personne, de présenter aux personnes un contenu
                            ou une activité comme étant illicite dans le but d’en obtenir le retrait ou d’en faire
                            cesser la diffusion, alors qu’elle sait cette information inexacte, est puni d’une peine
                            d’un an d’emprisonnement et de 15 000 euros d’amende ».
                        </p>

                        <button type="submit" className="button button--primary flag-modal__button">
                            ENVOYER
                        </button>
                        {this.state.errors.form && <p className="flag-modal__error">{this.state.errors.form}</p>}
                        <p className="flag-modal__subtext">
                            Les données recueillies sur ce formulaire sont traitées par LaREM afin de permettre aux
                            adhérents de contacter LaREM afin de signaler des comportements abusifs dans le cadre de
                            l’utilisation du service de l’Atelier des idées. Les informations marquées d'un astérisque
                            sont obligatoires. L'absence de réponse dans ces champs ne permettra pas à LaREM de traiter
                            votre demande. Conformément à la règlementation, vous disposez d'un droit d'opposition et
                            d'un droit à la limitation du traitement de données vous concernant, ainsi que d'un droit
                            d'accès, de rectification, de portabilité et d'effacement de vos données. Vous disposez
                            également de la faculté de donner des directives sur le sort de vos données après votre
                            décès. Vous pouvez exercer vos droits en nous adressant votre demande accompagnée d'une
                            copie de votre pièce d'identité à l'adresse postale ou électronique suivante : La République
                            En Marche, 63 rue Sainte-Anne, 75002 Paris, France et mes-donnees@en-marche.fr.
                        </p>
                    </form>
                )}
                {this.props.isSubmitSuccess && <SuccessSignal />}
                {this.props.isSubmitError && <ErrorModal submitAgain={this.submitForm} />}
            </React.Fragment>
        );
    }
}

FlagModal.defaultProps = {
    isSubmitSuccess: false,
    isSubmitError: false,
};

FlagModal.propTypes = {
    reasons: PropTypes.array.isRequired,
    onSubmit: PropTypes.func.isRequired,
    isSubmitSuccess: PropTypes.bool,
    isSubmitError: PropTypes.bool,
};

export default FlagModal;
