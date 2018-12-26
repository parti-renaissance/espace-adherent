import React from 'react';
import PropTypes from 'prop-types';

import ProposalSteps from './ProposalSteps';
import ProposalCriteria from './ProposalCriteria';

function Propose(props) {
    return (
        <div className="propose-page">
            <div className="l__wrapper">
                <ProposalSteps />
                <ProposalCriteria />
            </div>
        </div>
    );
}

export default Propose;
