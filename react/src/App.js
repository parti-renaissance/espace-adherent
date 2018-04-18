import React, { Component } from 'react';
import Header from './js/components/Header';
import Nav from './js/components/Nav';

import './App.css';

class App extends Component {
    render() {
        return (
            <div className="App">
                <Header name="Mickaël-Ange" departmentName="Indre-et-Loire" />
                <Nav />
            </div>
        );
    }
}

export default App;
