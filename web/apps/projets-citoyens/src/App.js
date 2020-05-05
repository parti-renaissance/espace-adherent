import React, { Component } from 'react';

import { connect } from 'react-redux';
import { ConnectedRouter } from 'react-router-redux';
import { Route } from 'react-router-dom';

import Sidebar from './js/components/Sidebar';
import CitizenProject from './js/containers/CitizenProject';
import CitizenProjectTurnKey from './js/containers/CitizenProjectTurnKey';
import CitizenProjectSearch from './js/containers/CitizenProjectSearch';

import { history } from './js/store';

const routes = [
    {
        path: '/projets-citoyens',
        exact: true,
        main: () => <CitizenProject />,
        item_label: 'A propos des projets citoyens',
        className: 'citizen-projects',
    },
    {
        path: '/projets-citoyens/cle-en-main',
        exact: true,
        main: () => <CitizenProjectTurnKey />,
        item_label: 'Découvrir les projets clé en main',
        className: 'decouvrir-citizen-projects',
    },
    {
        path: '/projets-citoyens/recherche',
        exact: true,
        main: () => <CitizenProjectSearch />,
        item_label: 'Explorer tous les projets',
        className: 'explorer-les-projects',
    },
];

class App extends Component {
    render() {
        return (
            <div className="App ">
                <ConnectedRouter history={history}>
                    <div className="citizen__layout ">
                        {/* <Markup
                            className="citizen__header"
                            markup={this.props.header}
                        /> */}

                        <Sidebar routes={routes} />

                        <div className="citizen__main l__wrapper ">
                            {routes.map((route, index) => (
                                <Route key={index} path={route.path} exact={route.exact} component={route.main} />
                            ))}
                        </div>
                    </div>
                </ConnectedRouter>
            </div>
        );
    }
}

const mapStateToProps = state => ({});

export default connect(mapStateToProps)(App);
