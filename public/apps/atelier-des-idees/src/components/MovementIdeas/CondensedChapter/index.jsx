import React from 'react';
import { Link } from 'react-router-dom';
import PropTypes from 'prop-types';
import icn_20px_left_arrow from './../../../img/icn_20px_left_arrow.svg';

function CondensedChapter(props) {
    return (
        <div className="condensed__wrapper">
            <Link to={props.link}>
                <h3>{props.title}</h3>
                <p>{props.description}</p>
                <span>
                    {props.linkLabel} <img src={icn_20px_left_arrow} alt="lien" />
                </span>
            </Link>
        </div>
    );
}

CondensedChapter.defaultProps = {
    title: '',
    description: '',
    linLabel: '',
    link: '',
};

CondensedChapter.propTypes = {
    title: PropTypes.array,
    description: PropTypes.array,
    linLabel: PropTypes.string,
    link: PropTypes.string,
};

export default CondensedChapter;
