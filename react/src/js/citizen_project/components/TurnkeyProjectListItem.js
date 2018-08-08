import React from 'react';

const TurnkeyProjectListItem = (props) => {
    const { title, subtitle, category } = props;
    return (
        <div className="turnkey__project__list--item">
            <span>{category}</span>
            <h2>{title}</h2>
            <p>{subtitle}</p>
        </div>
    );
};

export default TurnkeyProjectListItem;
