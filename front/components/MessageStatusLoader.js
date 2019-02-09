import React, { PropTypes } from 'react';

const MAX_API_CALLS = 100;

export default class MessageStatusLoader extends React.Component {
    constructor(props) {
        super(props);

        this.api = props.api;
        this.messageId = props.messageId;

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

    renderLoader() {
        return <p>
            Nous recherchons les adhérents correspondant à vos critères<br/>
            <img src="/images/loader-sm.gif" alt="loader"/>
        </p>;
    }

    renderActionBlock() {
        if (this.state.recipientCount) {
            return <div>
                <p>Votre filtre correspond à <b>{this.state.recipientCount} adhérent(s) !</b></p>
                <p>
                    <a href="./send" className="btn btn--blue">Envoyer</a>
                    <a
                        href="./visualiser"
                        className="btn--as-link btn--no-border b__nudge--left-large text--body text--blue--new"
                    >
                        Prévisualiser avant envoi
                    </a>
                </p>
            </div>;
        }

        return <div>
            <p>Votre filtre ne correspond à aucun adhérent !</p>
        </div>;
    }

    render() {
        if (this.state.synchronized) {
            this.componentWillUnmount();
        }

        if (this.state.calls >= MAX_API_CALLS) {
            this.componentWillUnmount();

            return <div>
                <p className="text--italic">
                    Nous n'avons pas encore terminé la recherche, veuillez revenir dans quelques instants.
                </p>
            </div>;
        }

        return (
            <div>
                {!this.state.synchronized ? this.renderLoader() : this.renderActionBlock()}
            </div>
        );
    }
}

MessageStatusLoader.defaultProps = {
    synchronized: false,
    recipientCount: null,
};

MessageStatusLoader.propsType = {
    api: PropTypes.object.isRequired,
    messageId: PropTypes.string.isRequired,
    synchronized: PropTypes.bool,
    recipientCount: PropTypes.integer,
};
