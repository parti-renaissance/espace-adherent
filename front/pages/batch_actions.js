import React from 'react';
import { render } from 'react-dom';
import BatchActionsWidget from '../components/BatchActionsWidget';

export default (wrapperSelector, checkboxSelector, mainCheckboxSelector, actions, api) => {
    render(
        <BatchActionsWidget
            checkboxSelector={checkboxSelector}
            mainCheckboxSelector={mainCheckboxSelector}
            actions={actions}
            api={api}
        />,
        dom(wrapperSelector)
    );
};
