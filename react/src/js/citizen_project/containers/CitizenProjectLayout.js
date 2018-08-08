import React from 'react';
import { ConnectedRouter } from 'react-router-redux';
import { BrowserRouter as Router, Route, Link } from 'react-router-dom';
import CitizenProject from './../containers/CitizenProject';
import CitizenProjectTurnKey from './../containers/CitizenProjectTurnKey';
import CitizenProjectSearch from './../containers/CitizenProjectSearch';
import { history } from './../../store';

const routes = [
    {
        path: '/projet-citoyen',
        exact: true,
        main: () => <CitizenProject />,
        item_label: 'A propos des projets citoyens',
        // borderColor: { border: '10px solid #ffd400' },
    },
    {
        path: '/projet-citoyen/decouvrir-les-projets-citoyens',
        exact: true,
        main: () => <CitizenProjectTurnKey />,
        item_label: 'Découvrir les projets clés en main',
        // borderColor: { border: '10px solid #7ed321' },
    },
    {
        path: '/projet-citoyen/explorer-tous-les-projets-citoyens',
        exact: true,
        main: () => <CitizenProjectSearch />,
        item_label: 'Explorer tous les projets',
        // borderColor: { border: '10px solid #ff4d89' },
    },
];

const Sidebar = () => (
    <ConnectedRouter history={history}>
        <div className="citizen__layout">
            <div className="citizen__sidebar">
                <ul>
                    {routes.map((route, index) => (
                        <Link to={route.path} key={index} style={route.borderColor} className="item__menu">
                            <li>{route.item_label}</li>
                        </Link>
                    ))}
                </ul>
            </div>
            <div className="citizen__main">
                {routes.map((route, index) => (
                    <Route key={index} path={route.path} exact={route.exact} component={route.main} />
                ))}
            </div>
        </div>
    </ConnectedRouter>
);

export default Sidebar;
