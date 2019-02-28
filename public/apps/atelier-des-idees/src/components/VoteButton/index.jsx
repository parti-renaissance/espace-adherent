import React, { Component } from 'react';
import classNames from 'classnames';

class VoteButton extends Component {
    constructor(props) {
        super(props);
        this.state = {};
    }

    render() {
        const { vote, onSelected, resetTimeout, classes, prefix } = this.props;
        return (
            <button
                key={vote.id}
                className={classNames(this.state.animate, ...classes)}
                onClick={() => {
                    this.setState({
                        animate: vote.isSelected ? 'down' : 'up',
                    });
                    onSelected(vote.id);
                    if (resetTimeout) { resetTimeout(); }
                }}
            >
                <span className={`${prefix}__name`}>{vote.name}</span>
                <span className={`${prefix}__count`}>{vote.count}</span>
                <span className={`${prefix}__flag vote-button-flag`}>
                    {'down' === this.state.animate ? '-' : '+'}1
                </span>
            </button>
        );
    }
}

export default VoteButton;
