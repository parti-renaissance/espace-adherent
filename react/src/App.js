import React, { Component } from 'react';

import { ConnectedRouter } from 'react-router-redux';
import { Route, Switch } from 'react-router-dom';

import CitizenProjectLayout from './js/citizen_project/containers/CitizenProjectLayout';

import { history } from './js/store';
import './App.css';

class App extends Component {
    render() {
        return (
            <div className="App">
                <ConnectedRouter history={history}>
                    <Switch>
                        <Route path="/projets-citoyens" component={CitizenProjectLayout} />
                        <Route path="/recherche/projets-citoyens" component={CitizenProjectLayout} />
                    </Switch>
                </ConnectedRouter>
            </div>
        );
    }
}

export default App;
