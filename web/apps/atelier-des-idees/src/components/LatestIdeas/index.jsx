import React from 'react';
import Tabs from '../Tabs/index';

function LatestIdeas(props) {
    const panes = [
        {
            title: 'Propositions finalisées',
            component: <div/>,
        },
    ];

    return (
        <div className="latest-ideas">
            <Tabs panes={panes}/>
        </div>
    );
}

export default LatestIdeas;