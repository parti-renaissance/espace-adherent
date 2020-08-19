import React, { PropTypes } from 'react';
import Loader from './Loader';
import ReqwestApiClient from '../services/api/ReqwestApiClient';

export default class TerritorialCouncilCandidacyWidget extends React.Component {
    constructor(props) {
        super(props);

        this.qualitySelect = dom(props.qualityFieldSelector);
        this.membershipInput = dom(props.membershipFieldSelector);
        this.submitButton = dom(props.submitButtonSelector);

        this.state = {
            isLoading: true,
            quality: this.qualitySelect.value,
            searchQuery: null,
            memberships: [],
        };

        this.handleSearchChange = this.handleSearchChange.bind(this);
        this.handleQualityChange = this.handleQualityChange.bind(this);
    }

    componentDidMount() {
        on(this.qualitySelect, 'change', this.handleQualityChange);

        this.loadMemberships();
    }

    render() {
        let content;

        if (this.state.isLoading) {
            content = <Loader wrapperClassName={'text--center space--30-0'} />;
            hide(this.submitButton);
        } else if (this.state.error) {
            content = <p className={'text--body text--gray text--small text--center'}>
                {this.state.error}
            </p>;
            hide(this.submitButton);
        } else {
            show(this.submitButton);
            content = (
                <div id="membership-container">
                    {this.state.memberships.map((membership, key) => (
                        <div className="form__radio" key={key}>
                            <input
                                type="radio"
                                name={'candidacy_quality[invitation][membership]'}
                                required="required" id={`membership_${membership.uuid}`} value={membership.uuid} />

                            <label className="form form__label required" htmlFor={`membership_${membership.uuid}`}>
                                <div className="l__row identity">
                                    <div
                                        className="avatar-initials avatar--small avatar--style-02 b__nudge--right-small"
                                    >
                                        {membership.adherent.initials}
                                    </div>
                                    <div>
                                        <div className="font-roboto">{membership.adherent.full_name}</div>
                                    </div>
                                </div>
                            </label>
                        </div>)
                        )}
                </div>
            );
        }

        return (
            <div>
                <div className="em-form__group">
                    <div className="em-form__field--ctn">
                        <input type="search" placeholder="Rechercher un membre..."
                               id="member-search"
                               className="em-form__field form form__field"
                               onChange={this.handleSearchChange}
                        />
                    </div>
                </div>

                {content}
            </div>
        );
    }

    loadMemberships() {
        const quality = this.state.quality;
        const query = this.state.searchQuery;

        this.props.api.getTerritorialCouncilAvailableMemberships(
            { quality, query },
            (data) => {
                if (quality !== this.state.quality || query !== this.state.searchQuery) {
                    return;
                }

                if (!Array.isArray(data) || 1 > data.length) {
                    this.setState({
                        isLoading: false,
                        error: 'Impossible de constituer un binôme, aucun membre n\'est disponible.',
                    });
                } else {
                    this.setState({
                        isLoading: false,
                        error: null,
                        memberships: data,
                    });
                }
            },
            (response) => {
                if (quality !== this.state.quality || query !== this.state.searchQuery) {
                    return;
                }

                this.setState({
                    isLoading: false,
                    error: response.message || 'Une erreur est survenue',
                });
            }
        );
    }

    handleSearchChange(event) {
        this.setState(
            {
                isLoading: true,
                searchQuery: event.target.value,
            },
            this.loadMemberships
        );
    }

    handleQualityChange(event) {
        this.setState(
            {
                isLoading: true,
                quality: event.target.value,
            },
            this.loadMemberships
        );
    }
}

TerritorialCouncilCandidacyWidget.propTypes = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
    qualityFieldSelector: PropTypes.string.isRequired,
    membershipFieldSelector: PropTypes.string.isRequired,
    submitButtonSelector: PropTypes.string.isRequired,
};
