import React from 'react';
import { NotMobile, Mobile } from '../../helpers/responsive';
import Slider from 'react-slick';
import hpMainIllustration from './../../img/hp-main-illustration.svg';

import MovementIdeasSection from './MovementIdeasSection/.';

const sectionContent = [
    {
        keyWord: 'propose',
        title: 'une idée',
        text: 'Soumettez une nouvelle proposition à la communauté.',
        linkLabel: 'Je propose',
        link: '/atelier-des-idees/proposer',
    },
    {
        keyWord: 'contribue',
        title: 'aux propositions',
        text: 'Enrichissez les propositions en cours d\'écriture.',
        linkLabel: 'Je contribue',
        link: '/atelier-des-idees/contribuer',
    },
    {
        keyWord: 'soutiens',
        title: 'des propositions',
        text: 'Donnez votre avis sur les propositions finalisées.',
        linkLabel: 'Je soutiens',
        link: '/atelier-des-idees/soutenir',
    },
];

const settingsSlider = {
    dots: true,
    infinite: true,
    speed: 500,
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
};

class MovementIdeas extends React.PureComponent {
    render() {
        const items = sectionContent.map(content => <MovementIdeasSection key={content.keyWord} {...content} />);
        return (
            <article className="movement-ideas">
                <div className="movement-ideas__first__section">
                    <div className="l__wrapper movement-ideas__first__section__in">
                        <h1 className="movement-ideas__first__section__in__title">Les idées des marcheurs</h1>
                        <p className="movement-ideas__first__section__in__content">
              Vous avez envie de contribuer à la réflexion du mouvement ? De proposer vos idées ? Avec l'Atelier des
              idées c'est possible !
                        </p>
                        <img
                            className="movement-ideas__first__section__in__main-illustration"
                            src={hpMainIllustration}
                            alt="Illustration"
                        />
                    </div>
                </div>
                <div className="l__wrapper">
                    <NotMobile>
                        <div className="movement-ideas__second__section">{items}</div>
                    </NotMobile>
                    <Mobile>
                        <Slider className="movement-ideas__slider" {...settingsSlider}>
                            {items}
                        </Slider>
                    </Mobile>
                </div>
            </article>
        );
    }
}

export default MovementIdeas;
