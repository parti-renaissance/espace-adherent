import React from 'react';
import { render } from 'react-dom';

import ReactDOM from 'react-dom';
import './App.css';
import App from './App';

import DashboardPage from './js/containers/DashboardPage';
import EventPage from './js/containers/EventPage';
import CommitteePage from './js/containers/CommitteePage';
import SendAMessagePage from './js/containers/SendAMessagePage';
import registerServiceWorker from './registerServiceWorker';
import { BrowserRouter as Router, Route, Link } from 'react-router-dom';

const router = (
    <Router>
        <div>
            <Route path="/" component={App} />
            <Route path="/dashboard-referent" component={DashboardPage} />
            <Route path="/event" component={EventPage} />
            <Route path="/committee" component={CommitteePage} />
            <Route path="/send-a-message" component={SendAMessagePage} />
        </div>
    </Router>
);

render(router, document.getElementById('root'));
// registerServiceWorker();
