import React from 'react';
import { render } from 'react-dom';
import TerritorialCouncilCandidacyWidget from '../components/TerritorialCouncilCandidacyWidget';

export default (api, qualityFieldSelector, submitButtonSelector, wrapperSelector) => {
    render(
        <TerritorialCouncilCandidacyWidget
            api={api}
            qualityFieldSelector={qualityFieldSelector}
            submitButtonSelector={submitButtonSelector}
        />,
        dom(wrapperSelector)
    );
};
