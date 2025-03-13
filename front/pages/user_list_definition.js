import React from 'react';
import { createRoot } from 'react-dom/client';
import UserListDefinitionWidget from '../components/UserListDefinitionWidget';

export default (memberType, type, wrapperSelector, checkboxSelector, mainCheckboxSelector, api, postApplyCallback) => {
    createRoot(dom(wrapperSelector)).render(
        <UserListDefinitionWidget
            memberType={memberType}
            type={type}
            checkboxSelector={checkboxSelector}
            mainCheckboxSelector={mainCheckboxSelector}
            api={api}
            postApplyCallback={postApplyCallback}
        />
    );
};
