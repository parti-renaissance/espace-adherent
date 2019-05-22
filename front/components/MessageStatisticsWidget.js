import React, { PropTypes } from 'react';
import Loader from './Loader';

export default class MessageStatisticsWidget extends React.Component {
    constructor(props) {
        super(props);

        this.api = props.api;

        this._hideModal = this._hideModal.bind(this);
        this.displayModal = this.displayModal.bind(this);

        this.state = {
            uuid: null,
            subject: null,
            display: false,
            dataLoaded: false,
            sent: 0,
            opens: 0,
            openRate: 0,
            clicks: 0,
            clickRate: 0,
            unsubscribe: 0,
            unsubscribeRate: 0,
        };
    }

    displayModal(data) {
        data.display = true;

        if (data.uuid !== this.state.uuid) {
            data.dataLoaded = false;
        }

        this.setState(data);
    }

    fetchData() {
        if (!this.state.uuid) {
            return;
        }

        this.api.getMessageStatistics(this.state.uuid, (data) => {
            this.setState({
                dataLoaded: true,
                sent: data.sent,
                opens: data.opens,
                openRate: data.open_rate,
                clicks: data.clicks,
                clickRate: data.click_rate,
                unsubscribe: data.unsubscribe,
                unsubscribeRate: data.unsubscribe_rate,
            });
        });
    }

    renderStatsBlock() {
        return (
            <div className="font-roboto space--30">
                <div><strong>Statistiques de l'e-mail:</strong></div>
                <div className="b__nudge--top-15 b__nudge--bottom-large text--dark">Objet : {this.state.subject}</div>

                <div className="l__row">
                    <div className="l__col l__col--half">
                        <div className="text--uppercase text--small text--silver-gray">Envoyé à</div>
                        <div>
                            <span className="text--medium-small">{this.state.sent}</span>
                            <span className="text--dark"> contact(s)</span>
                        </div>
                    </div>
                    <div className="l__col l__col--half">
                        <div className="text--uppercase text--small text--silver-gray">Taux d’ouverture</div>
                        <div>
                            <span className="text--medium-small">{this.state.openRate}%</span>
                            <span className="text--dark"> ({this.state.opens})</span>
                        </div>
                    </div>
                </div>
                <div className="l__row b__nudge--top-large">
                    <div className="l__col l__col--half">
                        <div className="text--uppercase text--small text--silver-gray">Taux de clique</div>
                        <div>
                            <span className="text--medium-small">{this.state.clickRate}%</span>
                            <span className="text--dark"> ({this.state.clicks})</span>
                        </div>
                    </div>
                    <div className="l__col l__col--half">
                        <div className="text--uppercase text--small text--silver-gray">Taux de désabonnement</div>
                        <div>
                            <span className="text--medium-small">{this.state.unsubscribeRate}%</span>
                            <span className="text--dark"> ({this.state.unsubscribe})</span>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    renderLoader() {
        return (
            <div style={{ width: '44px', margin: '0 auto' }}>
                <Loader />
            </div>
        );
    }

    _hideModal() {
        this.setState({
            display: false,
        });
    }

    render() {
        if (!this.state.dataLoaded) {
            this.fetchData();
        }

        return (
            <div className="em-modal" style={{ display: this.state.display ? 'block' : 'none' }}>
                <div className="modal-content" style={{ width: '700px' }}>
                    <span className="close" onClick={this._hideModal}/>
                    {this.state.dataLoaded ? this.renderStatsBlock() : this.renderLoader()}
                </div>
            </div>
        );
    }
}

MessageStatisticsWidget.propsType = {
    api: PropTypes.object.isRequired,
};
