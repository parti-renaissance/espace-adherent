import React from 'react';
import { render } from 'react-dom';
import TerritorialCouncilCandidacyWidget from '../components/TerritorialCouncilCandidacyWidget';

export default (api, qualityFieldSelector, membershipFieldSelector, submitButtonSelector, wrapperSelector) => {
    render(
        <TerritorialCouncilCandidacyWidget
            api={api}
            qualityFieldSelector={qualityFieldSelector}
            membershipFieldSelector={membershipFieldSelector}
            submitButtonSelector={submitButtonSelector}
        />,
        dom(wrapperSelector)
    );
};
