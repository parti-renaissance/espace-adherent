import React from 'react';
import { render } from 'react-dom';
import { Provider } from 'react-redux';

import ReactDOM from 'react-dom';
import './App.css';
import App from './App';
import { store } from './js/store';

render(
    <Provider store={store}>
        <App />
    </Provider>,
    document.getElementById('root')
);
// registerServiceWorker();
