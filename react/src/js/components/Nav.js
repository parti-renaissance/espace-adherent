import React from 'react';
import { Link } from 'react-router-dom';

const Nav = props => (
    <ul className="referent__nav">
        <Link to="/espace-referent/dashboard-referent">
            <li className="active">Tableau de bord</li>
        </Link>
        <a href={`${process.env.REACT_APP_API_URL}/espace-referent/evenements`}>
            <li>Evénement</li>
        </a>
        <a href={`${process.env.REACT_APP_API_URL}/espace-referent/comites`}>
            <li>Comités</li>
        </a>
        <a href={`${process.env.REACT_APP_API_URL}/espace-referent/utilisateurs`}>
            <li>Envoyer un message</li>
        </a>
    </ul>
);

export default Nav;
