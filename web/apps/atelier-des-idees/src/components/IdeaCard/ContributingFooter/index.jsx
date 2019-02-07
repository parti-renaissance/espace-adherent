import React from 'react';
import { Link } from 'react-router-dom';
import PropTypes from 'prop-types';
import icn_hourglass from './../../../img/icn_hourglass.svg';

function ContributingFooter(props) {
    return (
        <div className="contributing-footer">
            <div className="contributing-footer__remaining-days">
                <img className="contributing-footer__remaining-days__icon" src={icn_hourglass} />
                <span className="contributing-footer__remaining-days__text">
                    {`${props.remainingDays} jour${1 < props.remainingDays ? 's' : ''} restant${
                        1 < props.remainingDays ? 's' : ''
                    }`}
                </span>
            </div>
            <div className="contributing-footer__container">
                <Link
                    className="contributing-footer__container__link button button--primary button--lowercase"
                    to={props.link}
                >
                    + Je contribue
                </Link>
            </div>
        </div>
    );
}

ContributingFooter.propTypes = {
    remainingDays: PropTypes.number.isRequired,
    link: PropTypes.string.isRequired,
};

export default ContributingFooter;
