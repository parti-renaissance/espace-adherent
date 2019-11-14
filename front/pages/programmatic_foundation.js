import React from 'react';
import { render } from 'react-dom';
import ProgrammaticFoundation from '../components/ProgrammaticFoundation/ProgrammaticFoundation';

export default (wrapperSelector, api) => {
    render(
        <ProgrammaticFoundation api={api}/>,
        dom(wrapperSelector)
    );
};
