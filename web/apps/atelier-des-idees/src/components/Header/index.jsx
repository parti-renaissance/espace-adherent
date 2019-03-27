import React from 'react';
import PropTypes from 'prop-types';
import { Link, NavLink } from 'react-router-dom';
import arrowDown from './../../img/icn_arrow_down.svg';
import SubMenu from './../SubMenu';

class Header extends React.PureComponent {
    render() {
        const myWorkShopRoutes = [
            {
                label: 'Mes propositions',
                value: 'propositions',
                onClick: () => this.props.onMyIdeasBtnClicked('my_ideas'),
            },
            {
                label: 'Mes contributions',
                value: 'contribution',
                onClick: () => this.props.onMyIdeasBtnClicked('my_contributions'),
            },
            { label: 'Mon pseudo', value: 'pseudo', onClick: () => this.props.onMyNicknameClicked() },
        ];
        return (
            <section className="header">
                <div className="header__inner l__wrapper">
                    <div className="header__nav">
                        <NavLink exact className="header__item" to="/atelier-des-idees">
                            Vue d'ensemble
                        </NavLink>
                        <NavLink exact className="header__item" to="/atelier-des-idees/contribuer">
                            Les propositions
                        </NavLink>
                        <NavLink exact className="header__item" to="/atelier-des-idees/proposer">
                            Comment participer ?
                        </NavLink>
                    </div>
                    {this.props.isAuthenticated ? (
                        <div>
                            <SubMenu
                                className="header__item"
                                options={myWorkShopRoutes}
                                onSelect={key => myWorkShopRoutes[key].onClick()}
                                label={['Mon atelier', <img src={arrowDown} alt="ouvrir le sous-menu" />]}
                            />
                            <Link
                                to="/atelier-des-idees/creer-ma-proposition"
                                className="header__create-btn button button--primary">
                                J'ai une proposition
                            </Link>
                        </div>
                    ) : (
                        <a
                            href="/atelier-des-idees/creer-ma-proposition?anonymous_authentication_intention=/connexion"
                            className="header__create-btn button button--primary">
                            J'ai une proposition
                        </a>
                    )}
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
    onMyNicknameClicked: PropTypes.func.isRequired,
};

export default Header;
