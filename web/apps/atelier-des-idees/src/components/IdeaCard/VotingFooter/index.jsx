import React from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import icn_20px_thumb from './../../../img/icn_20px_thumb.svg';

class VotingFooter extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            toggleVotes: false,
        };
        this.timerId = null;
        // ref of voting footer
        this.footerRef = React.createRef();

        this.resetTimeout = this.resetTimeout.bind(this);
    }

    componentWillUnmount() {
        clearTimeout(this.timerId);
    }

    resetTimeout() {
        clearTimeout(this.timerId);
        this.timerId = setTimeout(() => {
            this.setState(
                {
                    toggleVotes: false,
                },
                () => this.props.onToggleVotePanel(false)
            );
        }, 5000);
    }

    render() {
        return (
            <div
                className={classnames('voting-footer', { 'voting-footer--open': this.state.toggleVotes })}
                ref={this.footerRef}
            >
                <div className="voting-footer__container">
                    {/* MOBILE ELEMENTS */}
                    <button
                        className="voting-footer__container__action-vote--mobile"
                        onClick={() =>
                            this.setState(
                                prevState => ({
                                    toggleVotes: !prevState.toggleVotes,
                                }),
                                () => {
                                    if (this.state.toggleVotes) {
                                        this.resetTimeout();
                                    } else {
                                        clearTimeout(this.timerId);
                                    }
                                }
                            )
                        }
                    >
                        <p className="voting-footer__container__action-vote--mobile__text">Je vote</p>
                        <div
                            className={classnames('voting-footer__container__action-vote--mobile__arrow', {
                                rotate: this.state.toggleVotes,
                            })}
                        />
                    </button>

                    {!this.state.toggleVotes && (
                        <p className="voting-footer__total-votes">{this.props.totalVotes} votes</p>
                    )}
                    {!this.state.toggleVotes ? (
                        <div className="voting-footer__container__action-vote">
                            <button
                                className="button--lowercase"
                                onClick={() =>
                                    this.setState({ toggleVotes: true }, () => {
                                        this.props.onToggleVotePanel(true);
                                        this.resetTimeout();
                                    })
                                }
                            >
                                <img className="voting-footer__container__action-vote__icon" src={icn_20px_thumb} />
                                Je vote
                            </button>
                        </div>
                    ) : (
                        <p className="voting-footer__container__action-vote__text">Je vote:</p>
                    )}
                </div>

                {/* VOTES BUTTONS */}
                {this.state.toggleVotes &&
                    this.props.votes.map(vote => (
                        <button
                            key={vote.id}
                            className={classnames('voting-footer__vote', {
                                'voting-footer__vote--selected': vote.isSelected,
                            })}
                            onClick={() => {
                                this.props.onSelected(vote.id);
                                this.resetTimeout();
                            }}
                        >
                            <span className="voting-footer__vote__name">{vote.name}</span>
                            <span className="voting-footer__vote__count">{vote.count}</span>
                        </button>
                    ))}
            </div>
        );
    }
}

VotingFooter.propTypes = {
    votes: PropTypes.arrayOf(
        PropTypes.shape({
            id: PropTypes.string.isRequired,
            name: PropTypes.string.isRequired,
            count: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
            isSelected: PropTypes.bool.isRequired,
        })
    ).isRequired,
    totalVotes: PropTypes.number.isRequired,
    onSelected: PropTypes.func.isRequired,
    onToggleVotePanel: PropTypes.func.isRequired,
};

export default VotingFooter;
