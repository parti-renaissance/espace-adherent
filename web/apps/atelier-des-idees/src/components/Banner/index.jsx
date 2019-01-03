import React from 'react';
import PropTypes from 'prop-types';

class Banner extends React.PureComponent {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <div className="banner">
                <div className="banner__container__close">
                    <button
                        className="banner__container__close button--transparent"
                        onClick={() => this.props.onClose()}
                    >
                        <img src="/assets/img/icn_close-white.svg" />
                    </button>
                </div>
                <div className="banner__container__titles">
                    <h3 className="banner__container__titles__title">
                        {this.props.name}
                    </h3>
                    <h4 className="banner__container__titles__subtitle">
						Du{' '}
                        {new Date(this.props.started_at).toLocaleDateString('fr-fr', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric',
                        })}{' '}
						au{' '}
                        {new Date(this.props.ended_at).toLocaleDateString('fr-fr', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric',
                        })}
                    </h4>
                </div>
                <div className="banner__container">
                    <a
                        href={this.props.url}
                        className="banner__container__link button--secondary"
                        target="_blank"
                    >
                        {`${this.props.linkLabel}${
                            this.props.response_time
                                ? ` (${this.props.response_time}MIN)`
                                : ''
                        }`}
                    </a>
                </div>
            </div>
        );
    }
}

Banner.defaultProps = {
    response_time: undefined,
    linkLabel: 'Je participe',
};

Banner.propTypes = {
    response_time: PropTypes.string,
    url: PropTypes.string.isRequired,
    linkLabel: PropTypes.string,
    started_at: PropTypes.string.isRequired,
    ended_at: PropTypes.string.isRequired,
    name: PropTypes.string.isRequired,
    onClose: PropTypes.func.isRequired,
};

export default Banner;
