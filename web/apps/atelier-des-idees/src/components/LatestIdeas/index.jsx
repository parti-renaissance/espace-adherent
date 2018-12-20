import React from 'react';
import Tabs from '../Tabs/index';
import LatestIdeasPane from './LatestIdeasPane';

function LatestIdeas(props) {
    const panes = [
        {
            title: 'Propositions finalisées',
            component: <LatestIdeasPane link="/atelier-des-idees/consulter" />,
        },
        {
            title: 'Propositions en cours d\'élaboration',
            component: <LatestIdeasPane link="/atelier-des-idees/contribuer" />,
        },
    ];

    return (
        <article className="latest-ideas">
            <div className="l__wrapper">
                <h2 className="latest-ideas__title">Consultez les dernières propostions publiées par nos adhérents</h2>
                <Tabs panes={panes} />
            </div>
        </article>
    );
}

export default LatestIdeas;
