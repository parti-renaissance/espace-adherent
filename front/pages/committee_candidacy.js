import React from 'react';
import { createRoot } from 'react-dom/client';
import CommitteeCandidacyWidget from '../components/CommitteeCandidacyWidget';

export default (api, slug, submitButtonSelector, wrapperSelector) => {
    createRoot(dom(wrapperSelector)).render(<CommitteeCandidacyWidget api={api} slug={slug} submitButtonSelector={submitButtonSelector}/>);
};
