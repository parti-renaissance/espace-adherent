import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from "react-redux";

import './App.css';
import App from './App';
import store from './redux/store/.';
import registerServiceWorker from './registerServiceWorker';

ReactDOM.render(
    <Provider store={store}>
        <App />
    </Provider>, document.getElementById('root')
);
registerServiceWorker();
