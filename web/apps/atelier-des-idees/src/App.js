import React, { Component } from 'react';
import { Route, Redirect, Switch } from 'react-router-dom';

import Home from './pages/Home';

import logo from './logo.svg';

class App extends Component {
    render() {
        return (
            <div className="App">
                <Switch>
                    <Route exact path="/" component={Home}></Route>
                </Switch>
            </div>
        );
    }
}

export default App;
