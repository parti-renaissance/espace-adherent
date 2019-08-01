import React from 'react';
import { Link } from 'react-router-dom';
import icn_20px_left_arrow from './../../../../img/icn_20px_left_arrow.svg';

const Steps = props => (
    <div className="proposal--step ">
        <div className="l__wrapper">
            <div className="left">
                <img src={props.picto} alt="pictogramme" />
                <div>
                    <h3>{props.title}</h3>
                    <p>{props.description}</p>
                    <Link to={props.link}>
                        <span>{props.linkLabel}</span> <img src={icn_20px_left_arrow} alt="lien" />
                    </Link>
                </div>
            </div>
            <img className="img" src={props.img} alt="icone" />
        </div>
    </div>
);

export default Steps;
