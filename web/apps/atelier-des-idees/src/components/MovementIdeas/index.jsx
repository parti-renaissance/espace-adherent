import React from 'react';
import { withRouter } from 'react-router-dom';

import Button from '../Button/.';

class MovementIdeas extends React.PureComponent {
    render() {
        return (
            <div className="movement-ideas">
                <div className="movement-ideas__first__section">
                    <h1 className="movement-ideas__first__section__title">Les idées du mouvement</h1>
                    <p className="movement-ideas__first__section__content">
                        Vous avez envie de contribuer aux idées du mouvement ?
                        <br/>
                        Avec l’Atelier des Idées c’est possible !
                    </p>
                </div>
                <div className="movement-ideas__second__section">
                    <div className="movement-ideas__second__section__item">
                        <h4 className="movement-ideas__second__section__item__title">
                            Je <span className="movement-ideas__second__section__item__title__main">vote</span> pour des idées
                        </h4>
                        <p className="movement-ideas__second__section__item__content">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                            Sed fringilla urna sed erat auctor, ac sodales mi commodo.
                        </p>
                        <Button
                            label="Je vote"
                            className="button--secondary movement-ideas__second__section__item__button"
                            onClick={() => this.props.history.push('/consulter')}/>
                    </div>
                    <div className="movement-ideas__second__section__item">
                        <h4 className="movement-ideas__second__section__item__title">
                            Je <span className="movement-ideas__second__section__item__title__main">contribue</span> aux idées
                        </h4>
                        <p className="movement-ideas__second__section__item__content">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                            Sed fringilla urna sed erat auctor, ac sodales mi commodo.
                        </p>
                        <Button
                            label="Je contribue"
                            className="button--secondary movement-ideas__second__section__item__button"
                            onClick={() => this.props.history.push('/contribuer')}/>
                    </div>
                    <div className="movement-ideas__second__section__item">
                        <h4 className="movement-ideas__second__section__item__title">
                            Je <span className="movement-ideas__second__section__item__title__main">propose</span> des idées
                        </h4>
                        <p className="movement-ideas__second__section__item__content">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                            Sed fringilla urna sed erat auctor, ac sodales mi commodo.
                        </p>
                        <Button
                            label="Je propose"
                            className="button--secondary movement-ideas__second__section__item__button"
                            onClick={() => this.props.history.push('/proposer')}/>
                    </div>
                </div>
            </div>
        );
    }
}

export default withRouter(MovementIdeas);