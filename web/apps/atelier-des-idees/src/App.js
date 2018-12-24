import React, { Component } from 'react';
import { Route, Switch } from 'react-router-dom';

// pages
import Home from './pages/Home';
import ThreeTabs from './pages/ThreeTabs';

// modal
import ModalRoot from './containers/ModalRoot';

import logo from './logo.svg';

class App extends Component {
    render() {
        return (
            <div className="App">
                <ModalRoot />
                <Switch>
                    <Route exact path="/atelier-des-idees" component={Home} />
                    <Route exact path="/atelier-des-idees/consulter" component={ThreeTabs} />
                    <Route exact path="/atelier-des-idees/contribuer" component={ThreeTabs} />
                    <Route exact path="/atelier-des-idees/proposer" component={ThreeTabs} />
                </Switch>
            </div>
        );
    }
}

export default App;
