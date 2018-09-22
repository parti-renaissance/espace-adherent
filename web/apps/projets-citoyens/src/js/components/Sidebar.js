import React from 'react';
import { NavLink } from 'react-router-dom';

export default ({ routes }) => (
    <div className="citizen__sidebar ">
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
    </div>
);
