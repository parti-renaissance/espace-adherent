import React from 'react';
import PropTypes from 'prop-types';

import ProposalSteps from './ProposalSteps';
import ProposalCriteria from './ProposalCriteria';
import PreProposal from './PreProposal';

function Propose(props) {
    return (
        <div className="propose-page">
            <ProposalSteps />
            <ProposalCriteria />
            <PreProposal />
        </div>
    );
}

export default Propose;
