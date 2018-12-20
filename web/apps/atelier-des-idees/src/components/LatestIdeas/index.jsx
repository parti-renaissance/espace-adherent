import React from 'react';
import Tabs from '../Tabs/index';

function LatestIdeas(props) {
    const panes = [
        {
            title: 'Propositions finalisées',
            component: <div />,
        },
        {
            title: 'Propositions en cours d\'élaboration',
            component: <div />,
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
