import React from 'react';
import { withRouter } from 'react-router-dom';

import MovementIdeasSection from './MovementIdeasSection/.';

const sectionContent = [
    {
        keyWord: 'vote',
        title: 'pour des idéees',
        text: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed fringilla urna sed erat auctor, ac sodales mi commodo.',
        linkLabel: 'Je vote',
        link: '/consulter',
    },
    {
        keyWord: 'contribue',
        title: 'aux idéees',
        text: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed fringilla urna sed erat auctor, ac sodales mi commodo.',
        linkLabel: 'Je contribue',
        link: '/contribuer',
    },
    {
        keyWord: 'propose',
        title: 'des idéees',
        text: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed fringilla urna sed erat auctor, ac sodales mi commodo.',
        linkLabel: 'Je propose',
        link: '/proposer',
    },
]

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
                    {
                        sectionContent.map(content => <MovementIdeasSection {...content}/>)
                    }
                </div>
            </div>
        );
    }
}

export default withRouter(MovementIdeas);