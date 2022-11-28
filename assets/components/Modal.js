import React from 'react';
import PropTypes from 'prop-types';

export default class Modal extends React.Component {
    constructor(props) {
        super(props);

        this.closeCallback = props.closeCallback;
        this.contentCallback = props.contentCallback;

        this.state = {
            display: props.display || true,
            content: props.content,
        };

        this.hideModal = this.hideModal.bind(this);
        this.renderContent = this.renderContent.bind(this);
    }

    render() {
        return (
            <div className={`re-modal ${this.props.side ? `re-modal--side-${this.props.side}` : ''}`}
                style={{ display: this.state.display ? 'block' : 'none' }}>
                <div className={'modal-background'}
                    {...(this.props.withClose ? { onClick: () => this.hideModal({ closed: true }) } : {})}
                ></div>
                <div className={'modal-content'}>
                    {this.props.withClose
                        ? <span className={'close'} onClick={() => this.hideModal({ closed: true })}/>
                        : ''
                    }
                    {this.renderContent()}
                </div>
            </div>
        );
    }

    hideModal(event = {}) {
        this.setState({ display: false });

        if (this.closeCallback) {
            this.closeCallback(event);
        }
    }

    renderContent() {
        if (this.state.content) {
            return (<div dangerouslySetInnerHTML={{ __html: this.state.content }}/>);
        }

        return this.contentCallback();
    }
}

Modal.defaultProps = {
    withClose: true,
};

Modal.propTypes = {
    content: PropTypes.string,
    side: PropTypes.oneOf(['left', 'right']),
    display: PropTypes.bool,
    closeCallback: PropTypes.func,
    contentCallback: PropTypes.func,
};
