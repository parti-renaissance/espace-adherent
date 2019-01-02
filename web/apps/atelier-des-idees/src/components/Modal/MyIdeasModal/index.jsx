import React from 'react';
import PropTypes from 'prop-types';
import Tabs from '../../Tabs';
import MyIdeas from './MyIdeas';
import MyContributions from './MyContributions';

const panes = [
    {
        title: 'Mes idées',
        component: () => <MyIdeas />,
    },
    {
        title: 'Mes contributions',
        component: () => <MyContributions />,
    },
];

function MyIdeasModal(props) {
    return (
        <div className="my-ideas-modal">
            <h2 className="my-ideas-modal__title">Mes idées</h2>
            <p className="my-ideas-modal__subtitle">
				Retrouvez ici toutes votes idées que ce soient celles dont vous êtes
				l’auteur ou bien celles celles auxquelles vous avez participé.
            </p>
            <Tabs
                panes={panes}
                defaultActiveKey={'my_ideas' === props.tabActive ? '0' : '1'}
            />
        </div>
    );
}

MyIdeasModal.defaultProps = {
    tabActive: 'my_ideas',
};

MyIdeasModal.propTypes = {
    tabActive: PropTypes.oneOf(['my_ideas', 'my_contributions']),
};

export default MyIdeasModal;
