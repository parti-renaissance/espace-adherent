import React from 'react';
import { Link } from 'react-router-dom';

class PreProposal extends React.PureComponent {
    render() {
        return (
            <article className="l__wrap">
                <div className="pre-proposal">
                    <div className="pre-proposal__container">
                        {/* TODO: Replace by the right img */}
                        <img
                            className="pre-proposal__container__img"
                            src="/assets/img/proposal_step_1.svg"
                        />
                    </div>
                    <div className="pre-proposal__container">
                        <h2 className="pre-proposal__container__title">
							Une petite étape avant de vous lancer
                        </h2>
                        <p className="pre-proposal__container__text">
							Avant de publier votre idée, vérifiez qu'elle ne soit pas déjà
							dans le programme d'Emmanuel Macron grâce à l'outil On l'a dit, On
							le fait !
                        </p>
                        <a
                            className="button button--primary pre-proposal__container__link"
                            href="https://transformer.en-marche.fr/fr"
                            target="_blank"
                        >
							Suivre la mise en œuvre du programme
                        </a>
                    </div>
                </div>
                {/* TODO: FAQ link to a venir */}
                <p className="pre-proposal__footer">
					Vous avez des questions ? Écrivez-nous à{' '}
                    <span className="pre-proposal__footer__highlighted">
						idees@en-marche.fr
                    </span>{' '}
					ou consultez notre{' '}
                    <Link
                        className="pre-proposal__footer__highlighted"
                        to="/atelier-des-idees/a-venir"
                        target="_blank"
                    >
						FAQ
                    </Link>
                </p>
            </article>
        );
    }
}

export default PreProposal;
