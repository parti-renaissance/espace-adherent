import React from 'react';
import { Link } from 'react-router-dom';

const Nav = props => (
    <ul className="referent__nav">
        <Link to="dashboard-referent">
            <li className="active">Tableau de bord</li>
        </Link>
        <Link to="/event-page">
            <li>Evénement</li>
        </Link>
        <Link to="/committee-page">
            <li>Comités</li>
        </Link>
        <Link to="/send-a-message">
            <li>Envoyer un message</li>
        </Link>
    </ul>
);

export default Nav;
