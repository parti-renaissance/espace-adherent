import React from 'react';
import PropTypes from 'prop-types';
import { Link } from 'react-router-dom';
import TextArea from '../../../TextArea';
import Select from '../../../Select';

class FirstForm extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <form class="first-form">
                <div class="first-form__section">
                    <h2 class="first-form__section__title">Soumettez votre note ici</h2>
                    {/* TODO: link ici and là */}
                    <p class="first-form__section__subtitle">
						Une fois ce dernier formulaire rempli, votre note pourra être
						enrichie{' '}
                        <Link
                            className="first-form__section__subtitle__link"
                            to="/atelier-des-idees/contribuer"
                            target="_blank"
                        >
							ici
                        </Link>{' '}
						par des contributions d’adhérents pendant 3 semaines. Passé ce
						délai, elle sera affichée{' '}
                        <Link
                            className="first-form__section__subtitle__link"
                            to="/atelier-des-idees/consulter"
                            target="_blank"
                        >
							là
                        </Link>{' '}
						et pourra être soumise aux votes des adhérents.
                    </p>
                </div>
                <div class="first-form__section">
                    <label class="first-form__section__label">
						Description de l’idée
                    </label>
                    <TextArea
                        maxLength={180}
                        placeholder="Décrivez votre idée (180 caractères max)"
                    />
                </div>
                <div class="first-form__section">
                    <label class="first-form__section__label">Thématique</label>
                    <Select
                        options={this.props.themeOptions}
                        placeholder="Choisissez la(es) thémathique(s) de votre note"
                        subtitle="Ajoutez plusieurs thémathiques si besoin, dans l'ordre de leur importance"
                        isMulti={true}
                    />
                </div>
                <div class="first-form__section">
                    <label class="first-form__section__label">
						Est-ce un projet national ou européen ?
                    </label>
                    <Select
                        options={this.props.localityOptions}
                        placeholder="Choisissez l'échelle de votre note"
                        subtitle="Pour un projet local, rapprochez-vous de votre référent via la rubrique contact du site de votre territoire"
                    />
                </div>
                <button className="first-form__button button--secondary">
					dernière étape →
                </button>
            </form>
        );
    }
}

FirstForm.propTypes = {
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
};

export default FirstForm;
