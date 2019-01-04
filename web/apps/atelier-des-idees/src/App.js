import React, { Component } from 'react';
import { Route, Switch } from 'react-router-dom';

// HoC
import withAuth from './hocs/withAuth';

// pages
import Home from './pages/Home';
import ThreeTabs from './pages/ThreeTabs';
import CreateIdea from './pages/CreateIdeaPage';
import IdeaPage from './pages/IdeaPage';

// modal
import ModalRoot from './containers/ModalRoot';

import logo from './logo.svg';

class App extends Component {
    render() {
        return (
            <div className="App">
                <ModalRoot />
                {/* TODO: improve Header handling using withoutHeader HoC (bug with connected-react-router for now) */}
                {/* <Header /> */}
                <Switch>
                    <Route exact path="/atelier-des-idees" component={Home} />
                    <Route exact path="/atelier-des-idees/consulter" component={ThreeTabs} />
                    <Route exact path="/atelier-des-idees/contribuer" component={ThreeTabs} />
                    <Route exact path="/atelier-des-idees/proposer" component={ThreeTabs} />
                    <Route exact path="/atelier-des-idees/creer-ma-note" component={withAuth(CreateIdea)} />
                    <Route exact path="/atelier-des-idees/note/:id" component={IdeaPage} />
                </Switch>
            </div>
        );
    }
}

export default App;
