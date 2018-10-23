import React from 'react';
import { connect } from 'react-redux';
import { NavLink } from 'react-router-dom';
import MediaQuery from 'react-responsive';
import Select from 'react-select';
import { history } from '../store';

const NavDropdown = connect(({ routing: { location } }, { routes }) => ({
    routes: routes.map(r => ({ ...r, isActive: r.path === location.pathname })),
}))(({ routes }) => {
    const activeRoute = routes.find(r => r.isActive);
    return <div className="NavDropdown--wrapper">
        <Select
            simpleValue
            searchable={false}
            clearable={false}
            onChange={path => history.push(path)}
            value={{ label: activeRoute.item_label, value: activeRoute.path }}
            options={routes.map(route => ({
                label: route.item_label,
                value: route.path,
            }))}
            className="NavDropdown"
        />
    </div>;
});

const Sidebar = ({ routes }) =>
    <div className="citizen__sidebar ">
        <MediaQuery maxWidth={650}>
            <NavDropdown routes={routes} />
        </MediaQuery>
        <MediaQuery minWidth={651}>
            <ul>
                {routes.map((route, index) => (
                    <li key={index}>
                        <NavLink
                            activeClassName="is-active"
                            exact
                            to={route.path}
                            className={`item__menu ${route.className}`}>
                            <span>{route.item_label}</span>
                        </NavLink>
                    </li>
                ))}
            </ul>
        </MediaQuery>
    </div>;

export default Sidebar;
