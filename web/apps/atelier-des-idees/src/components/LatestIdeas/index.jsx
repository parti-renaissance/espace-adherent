import React from 'react';
import PropTypes from 'prop-types';
import _ from 'lodash';
import { Link } from 'react-router-dom';
import IdeaCard from './../IdeaCard';

function LatestIdeas(props) {
    // Get all finalized ideas;
    const finalized = props.ideas.finalized.isLoading ? '' : props.ideas.finalized.items.map(idea => idea);
    const pending = props.ideas.pending.isLoading ? '' : props.ideas.pending.items.map(idea => idea);

    // / Merge and mix all the finalized and pending ideas;
    const allIdeas = [...finalized, ...pending];
    const mergeAllIdeas = _.shuffle(allIdeas);
    const mergeAllIdeasSecond = _.shuffle(mergeAllIdeas);

    for (let i = 0; 2 > i; i++) {
        allIdeas.push(...finalized, ...pending);
    }

    return (
        <article className="latest-ideas">
            <div className="l__wrapper">
                <div className="latest-ideas--header">
                    <h2 className="latest-ideas__title">Comment ça marche ?</h2>
                    <p>Plusieurs façons d'utiliser l'Atelier des idées, trouvez celle qui vous correspond le mieux !</p>
                    <div className="latest-ideas__pane__footer">
                        <Link
                            to={'/atelier-des-idees/proposer'}
                            className="button button--tertiary latest-ideas__pane__footer__btn">
                            En savoir plus
                        </Link>
                    </div>
                </div>
            </div>

            <div className="latest-ideas__slider">
                <div>
                    {0 < mergeAllIdeas.length
                        ? mergeAllIdeas.map((idea, i) => <IdeaCard {...idea} key={i} condensed />)
                        : 'Chargement'}
                </div>
            </div>
            <div className="latest-ideas__slider">
                <div className="latest-ideas__slider--second">
                    {0 < mergeAllIdeasSecond.length
                        ? mergeAllIdeasSecond.map((idea, i) => <IdeaCard {...idea} key={i} condensed />)
                        : 'Chargement'}
                </div>
            </div>
            {/* <Link to="atelier-des-idees/soutenir">
                <p className="link">
          Voir toutes les propositions finalisées <img src={icn_20px_left_arrow} />
                </p>
            </Link> */}
        </article>
    );
}

LatestIdeas.defaultProps = {
    ideas: {},
};

LatestIdeas.propTypes = {
    ideas: PropTypes.shape({
        finalized: PropTypes.shape({
            isLoading: PropTypes.bool,
            items: PropTypes.array,
        }),
        pending: PropTypes.shape({
            isLoading: PropTypes.bool,
            items: PropTypes.array,
        }),
        read: PropTypes.arrayOf(PropTypes.string), // array of uuids
    }),
    onVoteIdea: PropTypes.func,
};

export default LatestIdeas;
