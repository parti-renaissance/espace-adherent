import React from 'react';
import PropTypes from 'prop-types';
import { FacebookShareButton, TwitterShareButton, TelegramShareButton } from 'react-share';
import { FacebookIcon, TwitterIcon } from 'react-share';

class ShareButtons extends React.PureComponent {
    render() {
        const url = window.location.href;
        const iconProps = {
            size: 32,
            round: true,
            iconBgStyle: { fill: 'black' },
        };
        return (
            <div className="share-buttons">
                <span className="share-buttons__label">Partagez</span>
                <FacebookShareButton url={url} quote={this.props.title}>
                    <FacebookIcon {...iconProps} />
                </FacebookShareButton>
                <TwitterShareButton url={url} title={this.props.title}>
                    <TwitterIcon {...iconProps} />
                </TwitterShareButton>
                <TelegramShareButton url={url} title={this.props.title}>
                    <div className="share-buttons__icon share-buttons__icon--telegram" />
                </TelegramShareButton>
            </div>
        );
    }
}

ShareButtons.defaultProps = {
    title: '',
};

ShareButtons.propTypes = {
    title: PropTypes.string,
};

export default ShareButtons;
