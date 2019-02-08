import React from 'react';
import PropTypes from 'prop-types';
import Tabs from '../Tabs/index';
import LatestIdeasPane from './LatestIdeasPane';

function LatestIdeas(props) {
    const { finalized = {}, pending = {}, read = [] } = props.ideas;
    const panes = [
        {
            title: 'Propositions en cours d\'élaboration',
            component: (
                <LatestIdeasPane
                    link="/atelier-des-idees/contribuer"
                    ideas={pending.items}
                    isLoading={pending.isLoading}
                    readIdeas={read}
                />
            ),
        },
        {
            title: 'Propositions finalisées',
            component: (
                <LatestIdeasPane
                    link="/atelier-des-idees/soutenir"
                    ideas={finalized.items}
                    isLoading={finalized.isLoading}
                    onVoteIdea={props.onVoteIdea}
                    readIdeas={read}
                />
            ),
        },
    ];

    return (
        <article className="latest-ideas">
            <div className="l__wrapper">
                <h2 className="latest-ideas__title">Les dernières propositions des marcheurs</h2>
                <Tabs panes={panes} />
            </div>
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
