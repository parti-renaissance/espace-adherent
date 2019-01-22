import React from 'react';
import PropTypes from 'prop-types';
// import ScrollMenu from 'react-horizontal-scrolling-menu';
import { Link, NavLink } from 'react-router-dom';
// import { NotMobile, Mobile } from '../../helpers/responsive';
import Button from '../Button';

class Header extends React.PureComponent {
    render() {
        return (
            <section className="header">
                <div className="header__inner l__wrapper">
                    <div className="header__nav">
                        <NavLink exact className="header__item" to="/atelier-des-idees">
                            Vue d'ensemble
                        </NavLink>
                        {this.props.isAuthenticated && (
                            <React.Fragment>
                                <button
                                    className="header__item header__button"
                                    onClick={() => this.props.onMyIdeasBtnClicked('my_ideas')}
                                >
                                    Mes propositions
                                </button>
                                <button
                                    className="header__item header__button"
                                    onClick={() => this.props.onMyIdeasBtnClicked('my_contributions')}
                                >
                                    Mes contributions
                                </button>
                            </React.Fragment>
                        )}
                    </div>
                    <a
                        href="/atelier-des-idees/creer-ma-note?anonymous_authentication_intention=/connexion"
                        className="header__create-btn button button--primary"
                    >
                        J'ai une proposition
                    </a>
                    {/* <Mobile>
                <ScrollMenu
                    alignCenter={false}
                    arrowRight={<div>{'>'}</div>}
                    data={menuItems}
                    hideArrows={true}
                    hideSingleArrow={true}
                />
            </Mobile>*/}
                </div>
            </section>
        );
    }
}

Header.defaultProps = {
    isAuthenticated: false,
};

Header.propTypes = {
    isAuthenticated: PropTypes.bool,
    onMyIdeasBtnClicked: PropTypes.func.isRequired,
};

export default Header;
