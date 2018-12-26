import React from 'react';
import { Link } from 'react-router-dom';

class ProposalCriteria extends React.PureComponent {
    render() {
        return (
            <article className="l__wrapper">
                <div className="proposal-criteria">
                    <div className="proposal-criteria__container">
                        <h2 className="proposal-criteria__container__title">
							À quels critères doit répondre une bonne note ?
                        </h2>
                        <Link
                            to="/atelier-des-idees/creer-ma-note"
                            className="button button--primary proposal-criteria__container__link"
                        >
							Je rédige ma note
                        </Link>
                    </div>
                    <div className="proposal-criteria__container">
                        <div className="proposal-criteria__container__item">
                            <img
                                className="proposal-criteria__container__item__icon"
                                src="/assets/img/icn_checklist.svg"
                            />
                            <p className="proposal-criteria__container__item__text">
                                <span className="proposal-criteria__container__item__text__main">
									Être une vraie proposition :
                                </span>{' '}
								il ne s’agit pas uniquement d’une critique ou d’un constat quant
								à une situation existante.
                            </p>
                        </div>
                        <div className="proposal-criteria__container__item">
                            <img
                                className="proposal-criteria__container__item__icon"
                                src="/assets/img/icn_checklist.svg"
                            />
                            <p className="proposal-criteria__container__item__text">
                                <span className="proposal-criteria__container__item__text__main">
									Répondre à une politique publique :
                                </span>{' '}
								le problème visé doit être national, la réponse politique se
								situant au niveau de l’état (et non de la municipalité ou de la
								collectivité)
                            </p>
                        </div>
                        <div className="proposal-criteria__container__item">
                            <img
                                className="proposal-criteria__container__item__icon"
                                src="/assets/img/icn_checklist.svg"
                            />
                            <p className="proposal-criteria__container__item__text">
                                <span className="proposal-criteria__container__item__text__main">
									Répondre à un problème précis :
                                </span>{' '}
								la proposition a pour objet de répondre à un problème précis, un
								constat, une situation améliorable.
                            </p>
                        </div>
                        <div className="proposal-criteria__container__item">
                            <img
                                className="proposal-criteria__container__item__icon"
                                src="/assets/img/icn_checklist.svg"
                            />
                            <p className="proposal-criteria__container__item__text">
                                <span className="proposal-criteria__container__item__text__main">
									Être respectueuse et courtoise :
                                </span>{' '}
								votre proposition doit être conforme à notre charte des valeurs,
								nos CGU et respecter la législation en vigueur notamment
								relative à la diffamation, la prohibition des propos, injurieux,
								discriminatoires, raciste ou d&apos;incitation à commettre un
								délit ou un crime.
                            </p>
                        </div>
                    </div>
                </div>
            </article>
        );
    }
}

export default ProposalCriteria;
