import React from 'react';
import { Link } from 'react-router-dom';
import PropTypes from 'prop-types';
import hourglassIcnGreen from './../../../img/icn_hourglass_green.svg';

class ContributingFooter extends React.PureComponent {
    getRemainingDays() {
        return `${this.props.remainingDays} jour${1 < this.props.remainingDays ? 's' : ''} restant${
            1 < this.props.remainingDays ? 's' : ''
        }`;
    }

    render() {
        return (
            <div className="contributing-footer">
                <div className="contributing-footer__remaining-days">
                    <img className="contributing-footer__remaining-days__icon" src={hourglassIcnGreen} />
                    <span className="contributing-footer__remaining-days__text">
                        <span className="contributing-footer__remaining-days__text--pending">En cours</span> -{' '}
                        {this.getRemainingDays()}
                    </span>
                </div>
                <div className="contributing-footer__container">
                    <Link
                        className="contributing-footer__container__link button button--primary button--lowercase"
                        to={this.props.link}
                    >
                        + Je contribue
                    </Link>
                </div>
            </div>
        );
    }
}

ContributingFooter.propTypes = {
    remainingDays: PropTypes.number.isRequired,
    link: PropTypes.string.isRequired,
};

export default ContributingFooter;
