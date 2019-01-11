import React from 'react';
import { NotMobile, Mobile } from '../../helpers/responsive';
import Slider from 'react-slick';
import hpMainIllustration from './../../img/hp-main-illustration.svg';

import MovementIdeasSection from './MovementIdeasSection/.';

// TODO: Update text
const sectionContent = [
    {
        keyWord: 'vote',
        title: 'pour des idées',
        text:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed fringilla urna sed erat auctor, ac sodales mi commodo.',
        linkLabel: 'Je vote',
        link: '/atelier-des-idees/consulter',
    },
    {
        keyWord: 'contribue',
        title: 'aux idées',
        text:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed fringilla urna sed erat auctor, ac sodales mi commodo.',
        linkLabel: 'Je contribue',
        link: '/atelier-des-idees/contribuer',
    },
    {
        keyWord: 'propose',
        title: 'des idées',
        text:
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed fringilla urna sed erat auctor, ac sodales mi commodo.',
        linkLabel: 'Je propose',
        link: '/atelier-des-idees/proposer',
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
        const items = sectionContent.map(content => <MovementIdeasSection {...content} />);
        return (
            <article className="movement-ideas">
                <div className="movement-ideas__first__section">
                    <div className="l__wrapper movement-ideas__first__section__in">
                        <h1 className="movement-ideas__first__section__in__title">Les idées du mouvement</h1>
                        <p className="movement-ideas__first__section__in__content">
                            Vous avez envie de contribuer aux idées du mouvement ?
                            <br />
                            Avec l’Atelier des Idées c’est possible !
                        </p>
                        <img className="movement-ideas__first__section__in__main-illustration" src={hpMainIllustration} />
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
