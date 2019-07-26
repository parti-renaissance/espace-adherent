import React from "react";

const CitizenProjectItem = props => {
  const {
    thumbnail,
    title,
    subtitle,
    author,
    localisation,
    url,
    district
  } = props;

  return (
    <a
      href={url}
      className="citizen__project__item"
      target="_blank"
      rel="noopener noreferrer"
    >
      <div className="citizen__project__item__thumbnail">
        <div style={{ backgroundImage: `url(${thumbnail})` }} />
      </div>
      <div className="citizen__project__item__content">
        <h2>{title} </h2>
        <p className="district">{district}</p>
        <p>{subtitle}</p>
        <div className="citizen__project__item__footer">
          Par <span className="citizen__project__item__author">{author}</span> à{" "}
          <span>{localisation}</span>
        </div>
      </div>
    </a>
  );
};

export default CitizenProjectItem;
