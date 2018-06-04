import React, { Component } from 'react';

import { ConnectedRouter } from 'react-router-redux';
import { Route, Switch } from 'react-router-dom';

import Layout from './js/containers/Layout';
import DashboardPage from './js/containers/DashboardPage';

import { history } from './js/store';
import './App.css';

class App extends Component {
    render() {
        return (
            <div className="App">
                <ConnectedRouter history={history}>
                    <Layout>
                        <Switch>
                            <Route exact path="/app/espace-referent/dashboard-referent" component={DashboardPage} />
                        </Switch>
                    </Layout>
                </ConnectedRouter>
            </div>
        );
    }
}

export default App;
