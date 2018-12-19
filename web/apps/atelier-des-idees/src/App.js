import React, { Component } from 'react';
import { Route, Switch } from 'react-router-dom';

import Home from './pages/Home';
import Consult from './pages/Consult';
import Contribute from './pages/Contribute';
import Propose from './pages/Propose';

import logo from './logo.svg';

class App extends Component {
    render() {
        return (
            <div className="App">
                <Switch>
                    <Route exact path="/atelier-des-idees" component={Home}></Route>
                    <Route exact path="/atelier-des-idees/consulter" component={Consult}></Route>
                    <Route exact path="/atelier-des-idees/contribuer" component={Contribute}></Route>
                    <Route exact path="/atelier-des-idees/proposer" component={Propose}></Route>
                </Switch>
            </div>
        );
    }
}

export default App;
