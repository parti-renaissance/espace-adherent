import React from 'react';

const TurnkeyProjectListItem = (props) => {
    const { title, subtitle, category, onClick, isActive } = props;
    return (
        <button
          onClick={onClick}
          className={`turnkey__project__list--item${isActive ? ' is-active' : ''}`}>
            <span>{category}</span>
            <h2>{title}</h2>
            <p>{subtitle}</p>
        </button>
    );
};

export default TurnkeyProjectListItem;
