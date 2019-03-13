import React from 'react';
import step1 from './../../../img/proposal_step_1.svg';
import step2 from './../../../img/proposal_step_2.svg';
import step3 from './../../../img/proposal_step_3.svg';

class ProposalSteps extends React.PureComponent {
    componentDidMount() {
        window.scrollTo(0, 0);
    }
    render() {
        return (
            <div className="proposal-steps">
                <article className="l__wrapper">
                    <h2 className="proposal-steps__title">Comment ça marche ?</h2>
                    <div className="proposal-steps__container">
                        <div className="proposal-steps__container__step">
                            <img className="proposal-steps__container__step__img" src={step1} alt="Etape 1" />
                            <span className="proposal-steps__container__step__number">1</span>
                            <p className="proposal-steps__container__step__text">
                Écrivez votre proposition seul(e) ou en groupe. Vous pourrez enregistrer votre brouillon à tout moment
                et continuer plus tard.
                            </p>
                        </div>
                        <div className="proposal-steps__container__separator" />
                        <div className="proposal-steps__container__step">
                            <img className="proposal-steps__container__step__img" src={step2} alt="Etape 2" />
                            <span className="proposal-steps__container__step__number">2</span>
                            <p className="proposal-steps__container__step__text">
                Quand vous êtes prêt(e), publiez votre proposition. Pendant 10 jours, les marcheurs pourront vous
                suggérer des améliorations que vous serez libre d'accepter (ou non).
                            </p>
                        </div>
                        <div className="proposal-steps__container__separator" />
                        <div className="proposal-steps__container__step">
                            <img className="proposal-steps__container__step__img" src={step3} alt="Etape 3" />
                            <span className="proposal-steps__container__step__number">3</span>
                            <p className="proposal-steps__container__step__text">
                Une fois finalisée, les marcheurs pourront voter pour votre proposition.
                            </p>
                        </div>
                    </div>
                </article>
            </div>
        );
    }
}

export default ProposalSteps;
