import React from 'react';
import { Link } from 'react-router-dom';
import { sse1 } from './../utils/';

const Nav = props => (
    <div id="sse1">
        <div id="sses1">
            <ul className="referent__nav">
                <Link to="dashboard-referent">
                    <li>Tableau de bord</li>
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
        </div>
    </div>
);

export default Nav;
