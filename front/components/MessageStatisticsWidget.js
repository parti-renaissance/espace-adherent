import React from 'react';
import PropTypes from 'prop-types';
import Loader from './Loader';
import numberFormat from '../utils/number';
import ReqwestApiClient from '../services/api/ReqwestApiClient';

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
            <div className="font-roboto">
                <div className="text--bold text--default-large">Statistiques de l'email :</div>
                <div className="b__nudge--top-15 b__nudge--bottom-large text--dark">Objet : {this.state.subject}</div>

                <div className="l__row">
                    <div className="l__col l__col--half">
                        <div className="text--data-label">Envoyé à</div>
                        <div className="b__nudge--top-10">
                            <span className="text--data-value">{numberFormat(this.state.sent)}</span>
                            <span className="text--dark"> contact{1 < this.state.sent ? 's' : ''}</span>
                        </div>
                    </div>
                    <div className="l__col l__col--half">
                        <div className="text--data-label">Taux d’ouverture</div>
                        <div className="b__nudge--top-10">
                            <span className="text--data-value">{this.state.openRate}%</span>
                            <span className="text--dark"> ({numberFormat(this.state.opens)})</span>
                        </div>
                    </div>
                </div>
                <div className="l__row b__nudge--top-large">
                    <div className="l__col l__col--half">
                        <div className="text--data-label">Taux de clic</div>
                        <div className="b__nudge--top-10">
                            <span className="text--data-value">{this.state.clickRate}%</span>
                            <span className="text--dark"> ({numberFormat(this.state.clicks)})</span>
                        </div>
                    </div>
                    <div className="l__col l__col--half">
                        <div className="text--data-label">Taux de désabonnement</div>
                        <div className="b__nudge--top-10">
                            <span className="text--data-value">{this.state.unsubscribeRate}%</span>
                            <span className="text--dark"> ({numberFormat(this.state.unsubscribe)})</span>
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
                <div className="modal-background" onClick={this._hideModal}></div>
                <div className="modal-content">
                    <span className="close" onClick={this._hideModal}/>
                    {this.state.dataLoaded ? this.renderStatsBlock() : this.renderLoader()}
                </div>
            </div>
        );
    }
}

MessageStatisticsWidget.propTypes = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
};
