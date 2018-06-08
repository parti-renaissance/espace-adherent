import React from 'react';
import { render } from 'react-dom';
import { Provider } from 'react-redux';
import 'react-select/dist/react-select.css';
import { PersistGate } from 'redux-persist/integration/react';

import './App.css';
import App from './App';
import { store, persistor } from './js/store';

render(
    <Provider store={store}>
        <PersistGate loading={null} persistor={persistor}>
            <App />
        </PersistGate>
    </Provider>,
    document.getElementById('root')
);
