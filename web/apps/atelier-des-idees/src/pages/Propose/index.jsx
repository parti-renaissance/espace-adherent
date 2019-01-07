import React from 'react';
import ThreeTabsPage from '../ThreeTabs';
import ProposalSteps from './ProposalSteps';
import ProposalCriteria from './ProposalCriteria';
import PreProposal from './PreProposal';

function Propose(props) {
    return (
        <ThreeTabsPage
            title="Proposer une nouvelle idée"
            subtitle="Vous avez une idée que vous aimeriez voir émerger dans le débat public ? Ecrivez une note sur votre thème de prédilection !"
        >
            <div className="propose-page">
                <ProposalSteps />
                <ProposalCriteria />
                <PreProposal />
            </div>
        </ThreeTabsPage>
    );
}

export default Propose;
