import React from 'react';
import { Link } from 'react-router-dom';

const Nav = props => (
    <ul className="referent__nav">
        <Link to="/app/espace-referent/dashboard-referent">
            <li className="active">Tableau de bord</li>
        </Link>
        <Link to="/espace-referent/evenements">
            <li>Evénement</li>
        </Link>
        <Link to="/espace-referent/comites">
            <li>Comités</li>
        </Link>
        <Link to="/espace-referent/utilisateurs">
            <li>Envoyer un message</li>
        </Link>
    </ul>
);

export default Nav;
