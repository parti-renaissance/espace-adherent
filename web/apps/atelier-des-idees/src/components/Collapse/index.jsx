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
        const Component = this.props.title.component;
        return (
            <div
                className={classNames('collapse', {
                    'collapse--close': !this.state.isCollapsed,
                    'collapse--open': this.state.isCollapsed,
                })}
            >
                <div className="collapse__container">
                    {'function' === typeof Component ? <Component /> : Component}
                    {/* TODO: Replace by icon */}
                    {this.state.isCollapsed ? (
                        <button
                            className="button collapse__container__btn--open"
                            onClick={() =>
                                this.setState({ isCollapsed: !this.state.isCollapsed })
                            }
                        >
							-
                        </button>
                    ) : (
                        <button
                            className="button collapse__container__btn--close"
                            onClick={() =>
                                this.setState({ isCollapsed: !this.state.isCollapsed })
                            }
                        >
							+
                        </button>
                    )}
                </div>
                {this.props.children && this.state.isCollapsed && (
                    <div>{this.props.children}</div>
                )}
            </div>
        );
    }
}

Collapse.propTypes = {
    title: PropTypes.shape({
        component: PropTypes.oneOfType([PropTypes.node.isRequired, PropTypes.func]),
    }),
};

export default Collapse;
