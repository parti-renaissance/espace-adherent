import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import hideIcn from '../../img/icn_48px_tool_hide.svg';
import revealIcn from '../../img/icn_48px_tool_reveal_white.svg';

class Collapse extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isCollapsed: props.isOpen,
        };
    }

    render() {
        const Component = this.props.title;
        return (
            <div
                className={classNames('collapse', {
                    'collapse--close': !this.state.isCollapsed,
                    'collapse--open': this.state.isCollapsed,
                })}
            >
                <button
                    className="collapse__container"
                    onClick={() => this.setState(prevState => ({ isCollapsed: !prevState.isCollapsed }))}
                >
                    {'function' === typeof Component ? <Component /> : Component}
                    <div
                        className={classNames('collapse__container__btn', {
                            'collapse__container__btn--open': this.state.isCollapsed,
                            'collapse__container__btn--close': !this.state.isCollapsed,
                        })}
                    >
                        {this.state.isCollapsed ? <img src={hideIcn} alt="Cacher" /> : <img src={revealIcn} alt="Afficher" />}
                    </div>
                </button>
                {this.props.children && this.state.isCollapsed && <div>{this.props.children}</div>}
            </div>
        );
    }
}

Collapse.defaultProps = {
    isOpen: false,
};

Collapse.propTypes = {
    title: PropTypes.oneOfType([PropTypes.node.isRequired, PropTypes.func, PropTypes.string]).isRequired,
    isOpen: PropTypes.bool,
};

export default Collapse;
