import React from 'react';
// import ScrollMenu from 'react-horizontal-scrolling-menu';
import { NavLink } from 'react-router-dom';
// import { NotMobile, Mobile } from '../../helpers/responsive';
import Button from '../Button';

class Header extends React.PureComponent {
    render() {
        // const menuItems = [<NavLink to="/atelier-des-idees">Vue d'ensemble</NavLink>];
        const menuItems = [
            <NavLink exact className="header__item" to="/atelier-des-idees">
                Vue d'ensemble
            </NavLink>,
            <button className="header__item header__button">Mes notes</button>,
            <button className="header__item header__button">Mes contributions</button>,
        ];
        return (
            <section className="header">
                <div className="header__inner l__wrapper">
                    <div className="header__nav">{menuItems}</div>
                    <Button className="header__create-btn button--primary" label="Je rédige mon idée" />
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

export default Header;
