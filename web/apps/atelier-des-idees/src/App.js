import React, { Component } from 'react';
import { Route, Switch } from 'react-router-dom';

import withAuth from './hocs/withAuth';
// pages
import Home from './pages/Home';
import ThreeTabs from './pages/ThreeTabs';
import CreateIdea from './pages/CreateIdea/containers/CreateIdeaPage';

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
          <Route
            exact
            path="/atelier-des-idees/consulter"
            component={ThreeTabs}
          />
          <Route
            exact
            path="/atelier-des-idees/contribuer"
            component={ThreeTabs}
          />
          <Route
            exact
            path="/atelier-des-idees/proposer"
            component={ThreeTabs}
          />
          <Route
            exact
            path="/atelier-des-idees/creer-ma-note"
            component={withAuth(CreateIdea)}
          />
        </Switch>
      </div>
    );
  }
}

export default App;
