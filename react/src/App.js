import React, { Component } from 'react';

import { ConnectedRouter } from 'react-router-redux';
import { Route, Switch } from 'react-router-dom';

import Layout from './js/referent_dashboard/containers/Layout';

import DashboardPage from './js/referent_dashboard/containers/DashboardPage';
import CitizenProject from './js/citizen_project/containers/CitizenProject';

import { history } from './js/store';
import './App.css';

class App extends Component {
    render() {
        return (
            <div className="App">
                <ConnectedRouter history={history}>
                    <Layout>
                        <Switch>
                            <Route exact path="/espace-referent/dashboard-referent" component={DashboardPage} />
                        </Switch>
                        <Switch>
                            <Route exact path="/projet-citoyen" component={CitizenProject} />
                        </Switch>
                    </Layout>
                </ConnectedRouter>
            </div>
        );
    }
}

export default App;
