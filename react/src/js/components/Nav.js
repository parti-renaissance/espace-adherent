import React, { Component} from 'react';

const Nav = (props) => {
    return(
        <ul className="referent__nav">
            <li className="active">
                Tableau de bord
            </li>
            <li>
                Evénement
            </li>
            <li>
                Comités
            </li>
            <li>
                Envoyer un message
            </li>
        </ul>
    )
}

export default Nav;
