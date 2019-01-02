import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { Route, Switch } from 'react-router-dom';
import { connect } from 'react-redux';
import { selectShowHeader } from './redux/selectors/ui';

// HoC
import withAuth from './hocs/withAuth';
import withoutHeader from './hocs/withoutHeader';

// components
import Header from './components/Header';

// pages
import Home from './pages/Home';
import ThreeTabs from './pages/ThreeTabs';
import CreateIdea from './pages/CreateIdeaPage';

// modal
import ModalRoot from './containers/ModalRoot';

import logo from './logo.svg';

class App extends Component {
    render() {
        return (
            <div className="App">
                <ModalRoot />
                {this.props.showHeader && <Header />}
                <Switch>
                    <Route exact path="/atelier-des-idees" component={Home} />
                    <Route exact path="/atelier-des-idees/consulter" component={ThreeTabs} />
                    <Route exact path="/atelier-des-idees/contribuer" component={ThreeTabs} />
                    <Route exact path="/atelier-des-idees/proposer" component={ThreeTabs} />
                    <Route exact path="/atelier-des-idees/creer-ma-note" component={withAuth(CreateIdea)} />
                </Switch>
            </div>
        );
    }
}

App.propTypes = {
    showHeader: PropTypes.bool.isRequired,
};

function mapDispatchToProps(state) {
    return { showHeader: selectShowHeader(state) };
}

export default connect(
    mapDispatchToProps,
    {}
)(App);
