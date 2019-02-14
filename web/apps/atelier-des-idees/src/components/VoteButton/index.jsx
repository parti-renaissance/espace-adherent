import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';

class VoteButton extends React.Component {
    constructor(props) {
        super(props);
        this.state = {};
    }

    render() {
        const { vote, onSelected, resetTimeout, className } = this.props;
        return (
            <button
                key={vote.id}
                className={classNames('vote-button', this.state.animate, className, {
                    'vote-button--selected': vote.isSelected,
                })}
                onClick={() => {
                    this.setState({
                        animate: vote.isSelected ? 'down' : 'up',
                    });
                    onSelected(vote.id);
                    if (resetTimeout) {
                        resetTimeout();
                    }
                }}
            >
                <span className={'vote-button__name'}>{vote.name}</span>
                <span className={'vote-button__count'}>{vote.count}</span>
                <span className={'vote-button__flag'}>{'down' === this.state.animate ? '-' : '+'}1</span>
            </button>
        );
    }
}

VoteButton.defaultProps = {
    className: '',
};

VoteButton.propTypes = {
    className: PropTypes.string,
    onSelected: PropTypes.func.isRequired,
    vote: PropTypes.shape({
        count: PropTypes.number,
        isSelected: PropTypes.bool,
        name: PropTypes.string,
    }).isRequired,
};

export default VoteButton;
