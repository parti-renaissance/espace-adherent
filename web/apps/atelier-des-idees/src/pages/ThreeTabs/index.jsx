import React from 'react';
import PropTypes from 'prop-types';
import { NavLink, Route, Switch } from 'react-router-dom';

import Header from '../../components/Header';
import Consult from '../Consult';
import Contribute from '../Contribute';
import Propose from '../Propose';

const routes = [
    {
        path: '/atelier-des-idees/consulter',
        exact: true,
        title: () => (
            <TTHeader title="Les idées finalisées" subtitle="Consultez les idées devenues de vraies propositions !" />
        ),
        main: Consult,
    },
    {
        path: '/atelier-des-idees/contribuer',
        exact: true,
        title: () => (
            <TTHeader
                title="Contribuer aux idées en cours"
                subtitle="Explorez les idées en cours de vos concitoyens et enrichissez-les !"
            />
        ),
        main: Contribute,
    },
    {
        path: '/atelier-des-idees/proposer',
        exact: true,
        title: () => (
            <TTHeader
                title="Proposer une nouvelle idée"
                subtitle="Vous avez une idée que vous aimeriez voir émerger dans le débat public ? Ecrivez une note sur votre thème de prédilection !"
            />
        ),
        main: Propose,
    },
];

function TTHeader(props) {
    return (
        <React.Fragment>
            <h1>{props.title}</h1>
            {props.subtitle && <p className="tt-page__header__subtitle">{props.subtitle}</p>}
        </React.Fragment>
    );
}

function ThreeTabs(props) {
    return (
        <React.Fragment>
            <Header />
            <div className="tt-page">
                <div className="tt-page__header l__wrapper">
                    <div className="tt-page__header__title">
                        {routes.map((route, index) => (
                            <Route key={index} path={route.path} exact={route.exact} component={route.title} />
                        ))}
                    </div>
                    <div className="tt-page__header__nav">
                        <NavLink className="tt-page__header__nav-link" to="/atelier-des-idees/consulter">
                            Consulter
                        </NavLink>
                        <NavLink className="tt-page__header__nav-link" to="/atelier-des-idees/contribuer">
                            Contribuer
                        </NavLink>
                        <NavLink className="tt-page__header__nav-link" to="/atelier-des-idees/proposer">
                            Proposer
                        </NavLink>
                    </div>
                </div>
                <div className="tt-page__main">
                    {routes.map((route, index) => (
                        <Route key={index} path={route.path} exact={route.exact} component={route.main} />
                    ))}
                </div>
            </div>
        </React.Fragment>
    );
}

export default ThreeTabs;
