import React, { Component } from 'react';

import { connect } from 'react-redux';
import { ConnectedRouter } from 'react-router-redux';
import { Route } from 'react-router-dom';

import Sidebar from './js/components/Sidebar';
import CitizenProject from './js/containers/CitizenProject';
import CitizenProjectTurnKey from './js/containers/CitizenProjectTurnKey';
import CitizenProjectSearch from './js/containers/CitizenProjectSearch';

import { getServerMarkup } from './js/actions/dom';

import { history } from './js/store';
import './App.css';

// const Markup = ({ markup, className }) => (
//     <div className={className} dangerouslySetInnerHTML={{ __html: markup }} />
// );

const routes = [
    {
        path: '/projets-citoyens',
        exact: true,
        main: () => <CitizenProject />,
        item_label: 'A propos des projets citoyens',
        className: 'citizen-projects'
    },
    {
        path: '/projets-citoyens/decouvrir',
        exact: true,
        main: () => <CitizenProjectTurnKey />,
        item_label: 'Découvrir les projets clé en main',
        className: 'decouvrir-citizen-projects'
    },
    {
        path: '/projets-citoyens/recherche',
        exact: true,
        main: () => <CitizenProjectSearch />,
        item_label: 'Explorer tous les projets',
        className: 'explorer-les-projects'
    }
];

class App extends Component {
    componentDidMount() {
        this.props.dispatch(getServerMarkup());
    }
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
                                <Route
                                    key={index}
                                    path={route.path}
                                    exact={route.exact}
                                    component={route.main}
                                />
                            ))}

                            {/* <Markup
                                className="citizen__footer"
                                markup={this.props.footer}
                            /> */}
                        </div>
                    </div>
                </ConnectedRouter>
            </div>
        );
    }
}

const mapStateToProps = state => ({
    header: state.dom.header,
    footer: state.dom.footer
});

export default connect(mapStateToProps)(App);
