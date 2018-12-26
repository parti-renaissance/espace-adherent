import React from 'react';

class ProposalSteps extends React.PureComponent {
    render() {
        return (
            <div className="proposal-steps">
                <article className="l__wrapper">
                    <h2 className="proposal-steps__title">Les étapes de votre notes</h2>
                    <div className="proposal-steps__container">
                        <div className="proposal-steps__container__step">
                            <img
                                className="proposal-steps__container__step__img"
                                src="/assets/img/proposal_step_1.svg"
                            />
                            <span className="proposal-steps__container__step__number">1</span>
                            <p className="proposal-steps__container__step__text">
								Commencez à écrire votre idée à partir d’un document prêt à
								l’emploi.
                            </p>
                        </div>
                        <div className="proposal-steps__container__separator" />
                        <div className="proposal-steps__container__step">
                            <img
                                className="proposal-steps__container__step__img"
                                src="/assets/img/proposal_step_2.svg"
                            />
                            <span className="proposal-steps__container__step__number">2</span>
                            <p className="proposal-steps__container__step__text">
								Quand vous êtes (presque) prêt(e), n’oubliez pas de publier
								votre idée sur notre site. Vous aurez alors 3 semaines pour
								l’amender et recueillir le plus de contributions.
                            </p>
                        </div>
                        <div className="proposal-steps__container__separator" />
                        <div className="proposal-steps__container__step">
                            <img
                                className="proposal-steps__container__step__img"
                                src="/assets/img/proposal_step_3.svg"
                            />
                            <span className="proposal-steps__container__step__number">3</span>
                            <p className="proposal-steps__container__step__text">
								Passé ce délai, les équipes du pôle Idées étudieront votre
								proposition et pourront la mettre en avant sur notre site !
                            </p>
                        </div>
                    </div>
                </article>
            </div>
        );
    }
}

export default ProposalSteps;
