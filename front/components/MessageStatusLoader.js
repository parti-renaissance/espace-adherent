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
        return <p class className="space--30-0">
            Patientez pendant le chargement de vos adhérents<br/>
            <img src="/images/loader-sm.gif" alt="loader" className="b__nudge--top"/>
        </p>;
    }

    renderActionBlock() {
        if (this.state.recipientCount) {
            return <div>
                <p className="text--medium-small">
                    Votre filtre correspond à <
                    span className="text--bold text--blue--dark">{this.state.recipientCount}</span> adhérent !
                </p>
                <p>
                    <a href="./send" className="btn btn--blue btn--large-and-full b__nudge--top">Envoyer</a>
                    <a
                        href="./visualiser"
                        className="btn btn--ghosting--blue btn--large-and-full b__nudge--top-15"
                    >
                        Prévisualiser avant envoi
                    </a>
                </p>
            </div>;
        }

        return <div>
            <p className="text--medium-small">Votre filtre ne correspond à aucun adhérent !</p>
        </div>;
    }

    render() {
        if (this.state.synchronized) {
            this.componentWillUnmount();
        }

        if (this.state.calls >= MAX_API_CALLS) {
            this.componentWillUnmount();

            return <div>
                <p className="tips-information">
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
