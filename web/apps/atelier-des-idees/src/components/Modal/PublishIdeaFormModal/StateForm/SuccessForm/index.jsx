import React from 'react';

class SucessForm extends React.PureComponent {
    render() {
        return (
            <div className="success-form">
                <img src="/assets/img/icn_state_success.svg" />
                <h3 className="success-form__title">Votre note a bien été publiée</h3>
                <p className="success-form__subtitle">
					Votre idée va maintenant être enrichie par des adhérents pendant 3
					semaines. Vous pouvez leur montrer que vous prenez en compte leurs
					commentaires en cliquant sur{' '}
                    <span className="success-form__subtitle__approved">Approuver</span>.
					Vous devrez ensuite intégrer vous-même leurs contributions à votre
					partie.
                </p>
                <button className="success-form__button button--secondary">
					Voir la page
                </button>
                <img src="assets/img/how-to-approve.svg" />
            </div>
        );
    }
}

export default SucessForm;
