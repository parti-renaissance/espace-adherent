import React from 'react';

const Header = props => (
    <div className="header__title">
        <h2>
				Bonjour <span className="world--undeline">{props.name}</span>,
        </h2>
        <h2>
				bienvenue sur votre espace référent <span className="world-undeline">{props.departmentName}</span>.
        </h2>
    </div>
);

export default Header;
