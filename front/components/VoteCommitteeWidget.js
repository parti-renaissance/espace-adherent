import React, { PropTypes } from 'react';
import ReqwestApiClient from '../services/api/ReqwestApiClient';
import Modal from './Modal';
import Loader from './Loader';

export default class VoteCommitteeWidget extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            display: false,
            triggerApiCall: false,
            oldCheckedSwitcherData: null,
        };

        this.closeModalCallback = this.closeModalCallback.bind(this);
    }

    componentDidMount() {
        this.toggleAllSwitchers(true);

        const oldCheckedSwitcherData = this.findOldCheckedSwitcher();

        this.setState({
            display: true,
            oldCheckedSwitcherData,
            triggerApiCall: null === oldCheckedSwitcherData,
        });
    }

    render() {
        if (!this.state.display) {
            return null;
        }

        let contentCallback;
        let fromConfirm = false;

        if (this.state.triggerApiCall) {
            this.callApi();

            contentCallback = () => <Loader wrapperClassName={'text--center space--30-0'} title={'Sauvegarde...'} />;
        } else {
            fromConfirm = true;
            contentCallback = () => this.confirmContent(this.state.oldCheckedSwitcherData);
        }

        return (
            <Modal
                key={`modal-${fromConfirm}`}
                contentCallback={contentCallback}
                closeCallback={() => this.closeModalCallback(fromConfirm)}
            />
        );
    }

    callApi() {
        this.props.api.toggleCommitteeVoteStatus(
            this.props.switcher.dataset.committeeSlug,
            this.props.switcher.dataset.token,
            this.props.switcher.checked,
            (data) => {
                if ('OK' === data.status) {
                    this.toggleAllSwitchers(false);

                    this.setState({
                        display: false,
                        triggerApiCall: false,
                    });
                } else {
                    location.reload();
                }
            }
        );
    }

    findOldCheckedSwitcher() {
        for (const element of findAll(document, `${this.props.switchSelector}:checked`)) {
            if (element.dataset.committeeSlug !== this.props.switcher.dataset.committeeSlug) {
                return this.getStateFromSwitcher(element);
            }
        }

        return null;
    }

    confirmContent(oldSwitcherData) {
        return (
            <div className="font-roboto">
                <div className="text--bold text--default-large">Changement du comité de vote</div>
                <p className="b__nudge--top-15 b__nudge--bottom-large text--dark">
                    Vous êtes sur le point de changer votre comité de vote.
                    Vous ne pourrez plus voter dans le comité <strong>{oldSwitcherData.committeeTitle}</strong>,
                    êtes-vous sûr de vouloir maintenant voter dans le comité
                    <strong> {this.props.switcher.dataset.committeeTitle}</strong> ?
                </p>

                <div>
                    <button
                        className="btn btn--ghosting--blue toggleModal b__nudge--right-nano"
                        onClick={() => this.closeModalCallback(true)}
                    >
                        Annuler
                    </button>

                    <button
                        className={'btn btn--blue'}
                        onClick={() => this.handleConfirmClick()}
                    >
                        Confirmer
                    </button>
                </div>
            </div>
        );
    }

    handleConfirmClick() {
        this.state.oldCheckedSwitcherData.element.checked = false;

        this.setState({
            display: true,
            oldCheckedSwitcherData: null,
            triggerApiCall: true,
        });
    }

    getStateFromSwitcher(element) {
        return {
            element,
            committeeSlug: element.dataset.committeeSlug,
            committeeTitle: element.dataset.committeeTitle,
        };
    }

    closeModalCallback(fromConfirm) {
        if (fromConfirm) {
            this.props.switcher.checked = false;
            this.toggleAllSwitchers(false);
        }

        this.setState({ display: false });
    }

    toggleAllSwitchers(disabled) {
        findAll(document, this.props.switchSelector).forEach((element) => {
            element.disabled = disabled;
        });
    }
}

VoteCommitteeWidget.propTypes = {
    api: PropTypes.instanceOf(ReqwestApiClient).isRequired,
    switcher: PropTypes.object.isRequired,
    switchSelector: PropTypes.string.isRequired,
};
