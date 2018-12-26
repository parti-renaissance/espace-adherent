import React from 'react';
import PropTypes from 'prop-types';

import ProposalSteps from './ProposalSteps';

function Propose(props) {
    return (
        <div className="propose-page">
            <div className="l__wrapper">
                <ProposalSteps />
            </div>
        </div>
    );
}

export default Propose;
