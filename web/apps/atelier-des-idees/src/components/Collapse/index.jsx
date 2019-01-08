import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

class Collapse extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isCollapsed: false,
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
                <div className="collapse__container">
                    {'function' === typeof Component ? <Component /> : Component}
                    {this.state.isCollapsed ? (
                        <button
                            className="button collapse__container__btn--open"
                            onClick={() => this.setState({ isCollapsed: !this.state.isCollapsed })}
                        >
                            <img src="/assets/img/icn_48px_tool_hide.svg" />
                        </button>
                    ) : (
                        <button
                            className="button collapse__container__btn--close"
                            onClick={() => this.setState({ isCollapsed: !this.state.isCollapsed })}
                        >
                            <img src="/assets/img/icn_48px_tool_reveal.svg" />
                        </button>
                    )}
                </div>
                {this.props.children && this.state.isCollapsed && <div>{this.props.children}</div>}
            </div>
        );
    }
}

Collapse.propTypes = {
    title: PropTypes.oneOfType([PropTypes.node.isRequired, PropTypes.func, PropTypes.string]).isRequired,
};

export default Collapse;
