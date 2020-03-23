import React from 'react';
import { render } from 'react-dom';
import UserSegmentManager from '../components/UserSegmentWidget';

export default (segmentType, wrapperSelector, checkboxSelector, api, countMembers) => {
    render(
        <UserSegmentManager
            segmentType={segmentType}
            checkboxSelector={checkboxSelector}
            mainCheckbox={dom('#members-check-all')}
            api={api}
            countMembers={countMembers}
        />,
        dom(wrapperSelector)
    );
};
