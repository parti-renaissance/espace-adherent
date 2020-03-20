import React, { PropTypes } from 'react';

export default class Modal extends React.Component {
    constructor(props) {
        super(props);

        if (!props.content && !props.contentCallback) {
            throw new Error('You should provide the modal content or callback');
        }

        this.closeCallback = props.closeCallback;
        this.contentCallback = props.contentCallback;

        this.state = {
            display: props.display || true,
            content: props.content,
        };

        this.hideModal = this.hideModal.bind(this);
        this.handleContentLoaded = this.handleContentLoaded.bind(this);
        this.renderContent = this.renderContent.bind(this);
    }

    handleContentLoaded(content) {
        this.setState({
            content,
            contentLoaded: true,
        });
    }

    render() {
        return (
            <div className="em-modal" style={{ display: this.state.display ? 'block' : 'none' }}>
                <div className="modal-background" onClick={this.hideModal}></div>
                <div className="modal-content">
                    <span className="close" onClick={this.hideModal}/>
                    {this.renderContent()}
                </div>
            </div>
        );
    }

    hideModal() {
        this.setState({ display: false });

        if (this.closeCallback) {
            this.closeCallback();
        }
    }

    renderContent() {
        if (this.state.content) {
            return (<div dangerouslySetInnerHTML={{ __html: this.state.content }}/>);
        }

        return this.contentCallback();
    }
}

Modal.propsType = {
    content: PropTypes.string,
    display: PropTypes.bool,
    closeCallback: PropTypes.func,
    contentCallback: PropTypes.func,
};
