import React from 'react';
import { createRoot } from 'react-dom/client';
import ProgrammaticFoundation from '../components/ProgrammaticFoundation/ProgrammaticFoundation';

export default (wrapperSelector, api) => createRoot(dom(wrapperSelector)).render(<ProgrammaticFoundation api={api}/>);
