import React from 'react';
import { render } from 'react-dom';
import CommitteeCandidacyWidget from '../components/CommitteeCandidacyWidget';

export default (api, slug, membershipFieldSelector, submitButtonSelector, wrapperSelector) => {
    render(
        <CommitteeCandidacyWidget
            api={api}
            slug={slug}
            membershipFieldSelector={membershipFieldSelector}
            submitButtonSelector={submitButtonSelector}
        />,
        dom(wrapperSelector)
    );
};
