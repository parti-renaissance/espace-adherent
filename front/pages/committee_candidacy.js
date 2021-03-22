import React from 'react';
import { render } from 'react-dom';
import CommitteeCandidacyWidget from '../components/CommitteeCandidacyWidget';

export default (api, slug, submitButtonSelector, wrapperSelector) => {
    render(
        <CommitteeCandidacyWidget
            api={api}
            slug={slug}
            submitButtonSelector={submitButtonSelector}
        />,
        dom(wrapperSelector)
    );
};
