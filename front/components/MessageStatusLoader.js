import React, { PropTypes } from 'react';
import Loader from './Loader';
import numberFormat from '../utils/number';

const MAX_API_CALLS = 50;

export default class MessageStatusLoader extends React.Component {
    constructor(props) {
        super(props);

        this.api = props.api;
        this.messageId = props.messageId;
        this.withResetButton = props.withResetButton;

        this.state = {
            synchronized: props.synchronized,
            recipientCount: props.recipientCount,
            calls: 0,
        };
    }

    componentDidMount() {
        if (!this.state.synchronized) {
            this.timerId = setInterval(
                () => this.refreshMessageStatus(),
                5000 // each 5sec
            );
        }
    }

    componentWillUnmount() {
        if (this.timerId) {
            clearInterval(this.timerId);
        }
    }

    refreshMessageStatus() {
        this.api.getMessageStatus(
            this.messageId,
            (data) => {
                this.setState(
                    state => ({
                        synchronized: data.synchronized,
                        recipientCount: data.recipientCount,
                        calls: state.calls + 1,
                    })
                );
            },
            () => this.setState({ calls: MAX_API_CALLS })
        );
    }

    renderActionBlock() {
        if (this.state.recipientCount) {
            return <div>
                <p className="text--medium-small">
                    Vous allez envoyer un message à&nbsp;
                    <span className="text--bold text--blue--dark">
                        {numberFormat(this.state.recipientCount)}
                    </span>&nbsp;
                    adhérent{1 < this.state.recipientCount ? 's' : ''} !
                </p>
                <p>
                    <a href="./send" className="btn btn--blue btn--large-and-full b__nudge--top">Envoyer</a>
                    <a
                        href="./visualiser?f"
                        className="btn btn--ghosting--blue btn--large-and-full b__nudge--top-15"
                    >
                        Prévisualiser avant envoi
                    </a>
                </p>
            </div>;
        }

        return <div>
            <p className="text--medium-small">Votre filtre ne correspond à aucun adhérent !</p>
            {this.withResetButton ?
                <p>
                    <a href="./filtrer" className="btn btn--ghosting--blue btn--large-and-full b__nudge--top">
                        RECHARGER
                    </a>
                </p>
                : ''
            }
        </div>;
    }

    render() {
        if (this.state.synchronized) {
            this.componentWillUnmount();
        }

        if (this.state.calls >= MAX_API_CALLS) {
            this.componentWillUnmount();

            return <div>
                <p className="alert alert--tips">
                    Nous n'avons pas encore terminé la recherche, veuillez revenir dans quelques instants.
                </p>
            </div>;
        }

        return (
            <div>
                {!this.state.synchronized ? <Loader title="Chargement de vos contacts" /> : this.renderActionBlock()}
            </div>
        );
    }
}

MessageStatusLoader.defaultProps = {
    synchronized: false,
    recipientCount: null,
    withResetButton: false,
};

MessageStatusLoader.propsType = {
    api: PropTypes.object.isRequired,
    messageId: PropTypes.string.isRequired,
    synchronized: PropTypes.bool,
    recipientCount: PropTypes.integer,
    withResetButton: PropTypes.bool,
};
