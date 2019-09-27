import React from 'react';
import { render } from 'react-dom';
import UserSegmentManager from '../components/UserSegmentWidget';

export default (wrapperSelector, checkboxSelector, api) => {
    render(
        <UserSegmentManager
            checkboxes={findAll(document, checkboxSelector)}
            mainCheckbox={find(document, '#members-check-all')}
            api={api}
        />,
        dom(wrapperSelector)
    );
};
