import React, { Component } from 'react';
import { Route, Redirect, Switch } from 'react-router-dom';

import Home from './pages/Home';
import Consult from './pages/Consult.jsx';
import Contribute from './pages/Contribute';
import Propose from './pages/Propose';

import logo from './logo.svg';

class App extends Component {
    render() {
        return (
            <div className="App">
                <Switch>
                    <Route exact path="/" component={Home}></Route>
                    <Route exact path="/consulter" component={Consult}></Route>
                    <Route exact path="/contribuer" component={Contribute}></Route>
                    <Route exact path="/proposer" compoenent={Propose}></Route>
                </Switch>
            </div>
        );
    }
}

export default App;
