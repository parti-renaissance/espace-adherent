import React from 'react';
import PropTypes from 'prop-types';
import { ideaStatus } from '../../../constants/api';
import CreateIdeaActions from '../CreateIdeaActions';
import Switch from '../../../components/Switch';
import icn_close from '../../../img/icn_close.svg';

class IdeaPageHeader extends React.Component {
    render() {
        return (
            <section className="create-idea-page__header">
                <div className="create-idea-page__header__inner l__wrapper">
                    {this.props.showContent && (
                        <React.Fragment>
                            <button className="button create-idea-actions__back" onClick={this.props.onBackClicked}>
                                ← Retour
                            </button>
                            {this.props.status !== ideaStatus.FINALIZED && this.props.canToggleReadingMode && (
                                <Switch
                                    onChange={this.props.toggleReadingMode}
                                    checked={this.props.isReading}
                                    label="Passer en mode lecture"
                                />
                            )}
                            {this.props.isAuthenticated &&
                                (this.props.isAuthor ? (
                                    <CreateIdeaActions
                                        onDeleteClicked={this.props.onDeleteClicked}
                                        onPublishClicked={this.props.onPublishClicked}
                                        onSaveClicked={this.props.onSaveClicked}
                                        isDraft={this.props.status === ideaStatus.DRAFT}
                                        isSaving={this.props.isSaving}
                                        canPublish={
                                            this.props.status === ideaStatus.DRAFT ||
                                            this.props.status === ideaStatus.PENDING
                                        }
                                    />
                                ) : (
                                    <button
                                        className="button create-idea-actions__report"
                                        onClick={this.props.onReportClicked}
                                    >
                                        Signaler la proposition
                                    </button>
                                ))}
                        </React.Fragment>
                    )}
                </div>
                {this.props.showSaveBanner && (
                    <div className="create-idea-page__success-banner">
                        <span>Votre brouillon a bien été enregistré</span>
                        <button
                            className="create-idea-page__success-banner__close"
                            onClick={() => {
                                this.props.closeSaveBanner();
                                clearTimeout(this.saveBannerTimer);
                            }}
                        >
                            <img src={icn_close} />
                        </button>
                    </div>
                )}
            </section>
        );
    }
}

IdeaPageHeader.defaultProps = {
    canToggleReadingMode: false,
    isAuthenticated: false,
    isAuthor: false,
    isSaving: false,
    showContent: true,
    showSaveBanner: false,
};

IdeaPageHeader.propTypes = {
    canToggleReadingMode: PropTypes.bool,
    closeSaveBanner: PropTypes.func.isRequired,
    isAuthenticated: PropTypes.bool,
    isAuthor: PropTypes.bool,
    isSaving: PropTypes.bool,
    onBackClicked: PropTypes.func.isRequired,
    onDeleteClicked: PropTypes.func.isRequired,
    onPublishClicked: PropTypes.func.isRequired,
    onReportClicked: PropTypes.func.isRequired,
    onSaveClicked: PropTypes.func.isRequired,
    showContent: PropTypes.bool,
    showSaveBanner: PropTypes.bool,
    status: PropTypes.string,
    toggleReadingMode: PropTypes.func.isRequired,
};

export default IdeaPageHeader;
