import React from 'react';
import Header from './../../containers/Header';
import ProposalSteps from './ProposalSteps';
import ProposalCriteria from './ProposalCriteria';
import PreProposal from './PreProposal';

function Propose(props) {
    return (
        <React.Fragment>
            <Header />
            <div className="propose-page">
                <ProposalSteps />
                <ProposalCriteria />
                <PreProposal />
            </div>
        </React.Fragment>
    );
}

export default Propose;
