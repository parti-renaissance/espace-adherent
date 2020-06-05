import React, { PropTypes } from 'react';
import moment from 'moment';
import Modal from './Modal';
import Loader from './Loader';
import ReqwestApiClient from '../services/api/ReqwestApiClient';
import numberFormat from '../utils/number';

export default class CandidaciesListWidget extends Modal {
    constructor(props) {
        super(props);

        this.state = {
            ...this.state,
            ...{
                data: null,
                isLoaded: false,
            },
        };

        this.contentCallback = this.getModalContent.bind(this);
        this.handleApiResponse = this.handleApiResponse.bind(this);
    }

    componentDidMount() {
        if (null === this.state.data) {
            this.props.api.getCommitteeCandidacies(
                this.props.committeeUuid,
                this.handleApiResponse
            );
        }
    }

    handleApiResponse(data) {
        if (data) {
            this.setState({
                data,
                isLoaded: true,
            });
        }
    }

    getModalContent() {
        if (!this.state.isLoaded) {
            return <Loader wrapperClassName={'text--center space--30-0'}/>;
        }

        const col1 = [];
        const col2 = [];

        this.state.data.candidacies.forEach((candidacy, index) => {
            const template = <div key={index} className={'text--dark b__nudge--bottom-medium l__row'}>
                <div className='avatar-initials avatar--small avatar--style-01'>
                    {candidacy.photo ?
                        <img src={candidacy.photo} alt="photo" /> :
                        (candidacy.first_name.charAt(0) + candidacy.last_name.charAt(0)).toUpperCase()
                    }
                </div>
                <div className='l__col b__nudge--left-small'>
                    <div>{candidacy.first_name} {candidacy.last_name}</div>
                    <div className='text--smallest'>
                        Déclaré{'female' === candidacy.gender ? 'e ' : ' '}
                        candidat{'female' === candidacy.gender ? 'e ' : ' '}
                        le {moment(candidacy.created_at).format('DD/MM/YYYY')}
                    </div>
                </div>
            </div>;

            if (0 === index % 2) {
                col1.push(template);
            } else {
                col2.push(template);
            }
        });

        return (
            <div className="font-roboto">
                <div className="text--bold text--default-large b__nudge--bottom-large">Liste des candidat(e)s :</div>

                <div className="l__row">
                    <div className="l__col l__col--grow-1">
                        <div className="text--data-label">candidatures</div>
                        <div className="b__nudge--top-10">
                            <span className="text--data-value">{this.state.data.metadata.total}</span>
                        </div>
                    </div>

                    <div className="l__col l__col--grow-1">
                        <div className="text--data-label">dont hommes</div>
                        <div className="b__nudge--top-10">
                            <span className="text--data-value">{this.state.data.metadata.males}</span>
                            <span className="text--dark"> ({numberFormat(
                                0 === this.state.data.metadata.total ? 0
                                    : Math.round(
                                        (this.state.data.metadata.males * 100) / this.state.data.metadata.total
                                    )
                                )} %)
                            </span>
                        </div>
                    </div>

                    <div className="l__col l__col--grow-1">
                        <div className="text--data-label">dont femmes</div>
                        <div className="b__nudge--top-10">
                            <span className="text--data-value">{this.state.data.metadata.females}</span>
                            <span className="text--dark"> ({numberFormat(
                                0 === this.state.data.metadata.total ? 0
                                    : Math.round(
                                        (this.state.data.metadata.females * 100) / this.state.data.metadata.total
                                    )
                                )} %)
                            </span>
                        </div>
                    </div>
                </div>

                <p className="b__nudge--top-large">Détail :</p>
                <div className="l__row b__nudge--top-large">
                    <div className={'l__col l__col--half'}>{col1}</div>
                    <div className={'l__col l__col--half'}>{col2}</div>
                </div>
            </div>
        );
    }
}

CandidaciesListWidget.propTypes = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
    committeeUuid: PropTypes.string.isRequired,
};
