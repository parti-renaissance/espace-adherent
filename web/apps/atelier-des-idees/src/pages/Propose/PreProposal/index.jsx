import React from 'react';
import { Link } from 'react-router-dom';
import proposeIdeaImg from './../../../img/propose-your-idea.svg';

class PreProposal extends React.PureComponent {
    componentDidMount() {
        window.scrollTo(0, 0);
    }

    render() {
        return (
            <article className="l__wrapper">
                <div className="pre-proposal">
                    <div className="pre-proposal__container">
                        <img className="pre-proposal__container__img" src={proposeIdeaImg} />
                    </div>
                    <div className="pre-proposal__container">
                        <h2 className="pre-proposal__container__title">Demandez le programme !</h2>
                        <p className="pre-proposal__container__text">
							Un doute sur ce qui est déjà fait ou prévu ?<br/>Suivez la mise en œuvre du programme
							présidentiel.
                        </p>
                        <a
                            className="button button--quaternary"
                            href="https://transformer.en-marche.fr/fr"
                            target="_blank"
                        >
							Suivre l'avancée du programme
                        </a>
                    </div>
                </div>
                <p className="pre-proposal__footer">
					Vous avez des questions ou des suggestions à nous faire ?<br/>
                    Écrivez-nous à{' '}
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
