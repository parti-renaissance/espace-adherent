import React, { PropTypes } from 'react';
import Loader from './Loader';
import ReqwestApiClient from '../services/api/ReqwestApiClient';

export default class AdherentCommitteeList extends React.Component {
    constructor(props) {
        super(props);

        this.api = props.api;

        this._hideModal = this._hideModal.bind(this);
        this.displayModal = this.displayModal.bind(this);

        this.state = {
            uuid: null,
            adherent_name: null,
            display: false,
            dataLoaded: false,
            committees: [],
        };
    }

    displayModal(data) {
        data.display = true;

        if (data.uuid !== this.state.uuid) {
            data.dataLoaded = false;
        }

        this.setState(data);

        addClass(document.body, 'modal-open');
    }

    fetchData() {
        if (!this.state.uuid) {
            return;
        }

        this.api.getAdherentCommittees(this.state.uuid, (data) => {
            this.setState({
                dataLoaded: true,
                committees: data,
            });
        });
    }

    renderMainBlock() {
        return (
            <div className="adherent__committees font-roboto">
                <div className="text--bold text--default-large b__nudge--bottom">
                    Comité(s) suivi(s) par {this.state.adherent_name} :
                </div>
                {this.state.committees.map((membership, index) => (
                        <div key={index} className="adherent__committees--item">
                            <div>
                                <a className="link--no-decor link--blue--dark"
                                   href={`/comites/${membership.committee.slug}`}
                                   target="_blank"
                                >
                                    {membership.committee.name}
                                </a>
                            </div>
                            <div className="text--small text--silver-gray">
                                <span className="text--bold">{this.getMembershipLabel(membership.privilege)} </span>
                                depuis le {
                                    membership.subscriptionDate.split('T')[0].replace(
                                        /([0-9]{4})-([0-9]{2})-([0-9]{2})/,
                                        '$3/$2/$1'
                                    )
                            }
                            </div>
                        </div>
                    ))}
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
        this.setState({ display: false });

        removeClass(document.body, 'modal-open');
    }

    render() {
        if (!this.state.dataLoaded) {
            this.fetchData();
        }

        return (
            <div className="em-modal" style={{ display: this.state.display ? 'block' : 'none' }}>
                <div className="modal-background" onClick={this._hideModal} />
                <div className="modal-content">
                    <span className="close" onClick={this._hideModal}/>
                    {this.state.dataLoaded ? this.renderMainBlock() : this.renderLoader()}
                </div>
            </div>
        );
    }

    getMembershipLabel(privilege) {
        switch (privilege) {
        case 'SUPERVISOR':
            return 'Animateur 🏅';
        case 'HOST':
            return 'Co-animateur 🏅';
        default:
            return 'Membre';
        }
    }
}

AdherentCommitteeList.propsType = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
};
