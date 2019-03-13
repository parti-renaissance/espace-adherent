import React from 'react';
import { NotMobile, Mobile } from '../../helpers/responsive';
import Slider from 'react-slick';
import hpMainIllustration from './../../img/hp-main-illustration.svg';

import CondensedChapter from './CondensedChapter';

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
        const sectionContent = [
            {
                title: [<span className="underline">Rédiger</span>, <br />, 'une propostion'],
                description: ['Soumettez une nouvelle', <br />, 'proposition à la communauté.'],
                linkLabel: 'Je propose',
                link: '/atelier-des-idees/proposer',
            },
            {
                title: ['Les propositions', <br />, 'à ', <span className="underline"> enrichir</span>],
                description: [
                    'Enrichissez une des ',
                    <span className="total">{this.props.totalCount.pending.items.length}</span>,
                    <br />,
                    ' propositions en cours d\'écriture.',
                ],
                linkLabel: 'Je contribue',
                link: '/atelier-des-idees/contribuer',
            },
            {
                title: [<span className="underline">Voter</span>, <br />, 'les propositions'],
                description: [
                    'Donnez votre avis sur les ',
                    <span className="total">{this.props.totalCount.finalized.items.length}</span>,
                    <br />,
                    ' propositions finalisées.',
                ],
                linkLabel: 'Je soutiens',
                link: '/atelier-des-idees/soutenir',
            },
        ];
        const items = sectionContent.map((content, idx) => (
            <CondensedChapter title={content.keyWord} {...content} key={idx} />
        ));

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
