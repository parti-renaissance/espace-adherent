import React from 'react';
import { Link } from 'react-router-dom';

class PreProposal extends React.PureComponent {
    render() {
        return (
            <article className="l__wrapper">
                <div className="pre-proposal">
                    <div className="pre-proposal__container">
                        <img className="pre-proposal__container__img" src="/assets/img/propose-your-idea.svg" />
                    </div>
                    <div className="pre-proposal__container">
                        <h2 className="pre-proposal__container__title">Une petite étape avant de vous lancer</h2>
                        <p className="pre-proposal__container__text">
                            Avant de publier votre idée, vérifiez qu'elle ne soit pas déjà dans le programme d'Emmanuel
                            Macron grâce à l'outil On l'a dit, On le fait !
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
                <p className="pre-proposal__footer">
                    Vous avez des questions ? Écrivez-nous à{' '}
                    <a className="pre-proposal__footer__highlighted" href="mailto:atelier-des-idees@en-marche.fr">
                        atelier-des-idees@en-marche.fr
                    </a>{' '}
                    ou consultez notre{' '}
                    <a className="pre-proposal__footer__highlighted" href="https://aide.en-marche.fr/" target="_blank">
                        FAQ
                    </a>
                </p>
            </article>
        );
    }
}

export default PreProposal;
