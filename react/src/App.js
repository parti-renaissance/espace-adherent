import React, { Component } from 'react';

import { ConnectedRouter } from 'react-router-redux';
import { Route, Switch } from 'react-router-dom';

import DashboardReferentLayout from './js/referent_dashboard/containers/DashboardReferentLayout';

import { history } from './js/store';
import './App.css';

class App extends Component {
    render() {
        return (
            <div className="App">
                <ConnectedRouter history={history}>
                    <Switch>
                        <Route exact path="/espace-referent/dashboard-referent" component={DashboardReferentLayout} />
                    </Switch>
                </ConnectedRouter>
            </div>
        );
    }
}

export default App;
