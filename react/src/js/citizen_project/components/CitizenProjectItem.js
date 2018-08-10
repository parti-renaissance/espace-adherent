import React from 'react';

const CitizenProjectItem = (props) => {
    const { thumbnail, title, subtitle, author, localisation } = props;

    return (
        <div className="citizen__project__item">
            <div className="citizen__project__item__thumbnail" style={{ backgroundImage: `url(${thumbnail})` }} />
            <div className="citizen__project__item__content">
                <h2>{title}</h2>
                <p>{subtitle}</p>
                <div className="citizen__project__item__footer">
                    Par <span>{author}</span> à <span>{localisation}</span>
                </div>
            </div>
        </div>
    );
};

export default CitizenProjectItem;
