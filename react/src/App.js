import React, { Component } from 'react';

import { ConnectedRouter } from 'react-router-redux';
import { Route, Switch } from 'react-router-dom';

import ReferentDashboardLayout from './js/referent_dashboard/containers/ReferentDashboardLayout';

import DashboardPage from './js/referent_dashboard/containers/DashboardPage';
import { history } from './js/store';
import './App.css';

class App extends Component {
    render() {
        return (
            <div className="App">
                <ConnectedRouter history={history}>
                    <Switch>
                        <ReferentDashboardLayout>
                            <Route exact path="/espace-referent/dashboard-referent" component={DashboardPage} />
                        </ReferentDashboardLayout>
                    </Switch>
                </ConnectedRouter>
            </div>
        );
    }
}

export default App;
