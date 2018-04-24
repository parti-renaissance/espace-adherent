import React, { Component } from 'react';

import { ConnectedRouter } from 'react-router-redux';
import { Route, Switch } from 'react-router-dom';

import Layout from './js/containers/Layout';
import DashboardPage from './js/containers/DashboardPage';
import EventPage from './js/containers/EventPage';
import CommitteePage from './js/containers/CommitteePage';
import SendAMessagePage from './js/containers/SendAMessagePage';

import { history } from './js/store';
import './App.css';

class App extends Component {
    render() {
        return (
            <div className="App">
                <ConnectedRouter history={history}>
                    <Layout>
                        <Switch>
                            <Route exact path="/dashboard-referent" component={DashboardPage} />
                            <Route path="/event-page" component={EventPage} />
                            <Route path="/committee-page" component={CommitteePage} />
                            <Route path="/send-a-message" component={SendAMessagePage} />
                        </Switch>
                    </Layout>
                </ConnectedRouter>
            </div>
        );
    }
}

export default App;
