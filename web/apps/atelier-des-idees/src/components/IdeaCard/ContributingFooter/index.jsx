import React from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import { Link } from 'react-router-dom';
import hourglassIcnGreen from './../../../img/icn_hourglass_green.svg';
import greenCheckIcn from '../../../img/icn_checklist.svg';

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
                        className={classnames(
                            'contributing-footer__container__link',
                            'button button--primary button--lowercase',
                            {
                                'contributing-footer__container__link--active': this.props.hasUserContributed,
                            }
                        )}
                        to={this.props.link}
                    >
                        {this.props.hasUserContributed ? (
                            <React.Fragment>
                                <img src={greenCheckIcn} className="contributing-footer__container__link__icon" />
                                J'ai contribué
                            </React.Fragment>
                        ) : (
                            '+ Je contribue'
                        )}
                    </Link>
                </div>
            </div>
        );
    }
}

ContributingFooter.defaultProps = {
    hasUserContributed: false,
};

ContributingFooter.propTypes = {
    hasUserContributed: PropTypes.bool,
    link: PropTypes.string.isRequired,
    remainingDays: PropTypes.number.isRequired,
};

export default ContributingFooter;
