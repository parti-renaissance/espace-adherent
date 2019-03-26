import React from 'react';

class ProposalCriteria extends React.PureComponent {
    componentDidMount() {
        window.scrollTo(0, 0);
    }
    render() {
        return (
            <article className="l__wrapper">
                <div className="proposal-criteria">
                    <div className="proposal-criteria__container">
                        <h2 className="proposal-criteria__container__title">
              Vous voulez vous lancer&nbsp;? Voici quelques conseils avant de commencer
                        </h2>
                        <a
                            href="/atelier-des-idees/creer-ma-proposition?anonymous_authentication_intention=/connexion"
                            className="button button--primary proposal-criteria__container__link"
                        >
              J'ai une proposition
                        </a>
                    </div>
                    <div className="proposal-criteria__container">
                        <div className="proposal-criteria__container__item">
                            <p className="proposal-criteria__container__item__text">
                                <span className="proposal-criteria__container__item__text__main">
                  Votre proposition apporte une réponse :
                                </span>{' '}
                elle doit dépasser le constat.
                            </p>
                        </div>
                        <div className="proposal-criteria__container__item">
                            <p className="proposal-criteria__container__item__text">
                                <span className="proposal-criteria__container__item__text__main">
                  Votre proposition est généralisable :
                                </span>{' '}
                votre idée peut s’appliquer sur tout le territoire français ou européen. Pour les idées qui concernent
                spécifiquement votre territoire, rapprochez vous de votre comité local ou référent !
                            </p>
                        </div>
                        <div className="proposal-criteria__container__item">
                            <p className="proposal-criteria__container__item__text">
                                <span className="proposal-criteria__container__item__text__main">Votre proposition est nouvelle :</span>{' '}
                il ne s’agit pas d’une idée déjà publiée ou mise en œuvre. Pensez à consulter les&nbsp;
                                <a href="/atelier-des-idees/contribuer" target="_blank">
                  idées en cours
                                </a>
                &nbsp;ou déjà&nbsp;
                                <a href="/atelier-des-idees/soutenir" target="_blank">
                  finalisées
                                </a>
                &nbsp; ainsi que le programme présidentiel.
                            </p>
                        </div>
                        <div className="proposal-criteria__container__item">
                            <p className="proposal-criteria__container__item__text">
                                <span className="proposal-criteria__container__item__text__main">
                  Votre proposition est en accord avec notre{' '}
                                    <a
                                        href="https://storage.googleapis.com/en-marche-prod/documents/adherents/1-charte-et-manifeste/charte_des_valeurs.pdf"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                    Charte des valeurs
                                    </a>
                                </span>{' '}
                et respecte nos&nbsp;
                                <a href="/atelier-des-idees/conditions-generales-utilisation" target="_blank">
                  Conditions Générales d'Utilisation
                                </a>
                .{' '}
                            </p>
                        </div>
                    </div>
                </div>
            </article>
        );
    }
}

export default ProposalCriteria;
