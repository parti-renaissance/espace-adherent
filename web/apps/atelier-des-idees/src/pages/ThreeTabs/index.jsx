import React from 'react';
import PropTypes from 'prop-types';
import { NavLink } from 'react-router-dom';
import Header from '../../containers/Header';

function ThreeTabs(props) {
    return (
        <React.Fragment>
            <Header />
            <div className="tt-page">
                <div className="tt-page__header l__wrapper">
                    <div className="tt-page__header__title">
                        <h1>{props.title}</h1>
                        {props.subtitle && <p className="tt-page__header__subtitle">
                            {props.subtitle}<br/>
                            {props.subtitleSub}</p>}
                    </div>
                    <div className="tt-page__header__nav">
                        <NavLink className="tt-page__header__nav-link" to="/atelier-des-idees/proposer">
                            Proposer
                        </NavLink>
                        <NavLink className="tt-page__header__nav-link" to="/atelier-des-idees/contribuer">
                            Contribuer
                        </NavLink>
                        <NavLink className="tt-page__header__nav-link" to="/atelier-des-idees/soutenir">
                            Soutenir
                        </NavLink>
                    </div>
                </div>
                <div className="tt-page__main">{props.children}</div>
            </div>
        </React.Fragment>
    );
}

ThreeTabs.defaultProps = {
    subtitle: '',
};

ThreeTabs.propTypes = {
    title: PropTypes.string.isRequired,
    subtitle: PropTypes.string,
};

export default ThreeTabs;
