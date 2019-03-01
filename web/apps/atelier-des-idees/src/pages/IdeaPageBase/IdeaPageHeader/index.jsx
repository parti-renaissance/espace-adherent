import React from 'react';
import PropTypes from 'prop-types';
import { ideaStatus } from '../../../constants/api';
import CreateIdeaActions from '../CreateIdeaActions';
import Switch from '../../../components/Switch';
import ShareButtons from '../../../components/ShareButtons';
import Dropdown from '../../../components/Dropdown';
import icn_close from '../../../img/icn_close.svg';

class IdeaPageHeader extends React.Component {
  render() {
    return (
      <section className="idea-page-header">
        <div className="idea-page-header__inner l__wrapper">
          {this.props.showContent && (
            <React.Fragment>
              <button className="button idea-page-header__back" onClick={this.props.onBackClicked}>
                ← Retour
              </button>
              {this.props.status !== ideaStatus.FINALIZED && this.props.canToggleReadingMode && (
                <Switch
                  onChange={this.props.toggleReadingMode}
                  checked={this.props.isReading}
                  label={this.props.isReading ? 'Mode lecture activé' : 'Mode lecture désactivé'}
                />
              )}
              {this.props.isAuthor && (
                <CreateIdeaActions
                  onDeleteClicked={this.props.onDeleteClicked}
                  onPublishClicked={this.props.status === ideaStatus.DRAFT && this.props.onPublishClicked}
                  onSaveClicked={this.props.status === ideaStatus.DRAFT && this.props.onSaveClicked}
                  isSaving={this.props.isSaving}
                  status={this.props.status}
                />
              )}
              {this.props.status !== ideaStatus.DRAFT && (
                <div className="idea-page-header__right">
                  <ShareButtons
                    title={`Consultez cette proposition "${
                      this.props.ideaTitle
                    }" faite sur l’Atelier des idées de La République En Marche !`}
                  />
                  {this.props.isAuthenticated &&
                    (!this.props.isAuthor && (
                      <Dropdown
                        className="idea-page-header__report"
                        onSelect={this.props.onReportClicked}
                        options={[{ value: 'report', label: 'Signaler', isImportant: true }]}
                      />
                    ))}

                  {this.props.isAuthenticated && this.props.isAuthor && (
                    <Dropdown
                      className="idea-page-header__report"
                      onSelect={() => this.props.onDeleteClicked()}
                      options={[
                        {
                          value: 'delete',
                          label: 'Supprimer la proposition',
                          isImportant: true
                        }
                      ]}
                    />
                  )}
                </div>
              )}
            </React.Fragment>
          )}
        </div>
        {this.props.showSaveBanner && (
          <div className="idea-page-header__success-banner">
            <span>Votre brouillon a bien été enregistré</span>
            <button
              className="idea-page-header__success-banner__close"
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
  ideaTitle: '',
  isAuthenticated: false,
  isAuthor: false,
  isSaving: false,
  showContent: true,
  showSaveBanner: false
};

IdeaPageHeader.propTypes = {
  canToggleReadingMode: PropTypes.bool,
  closeSaveBanner: PropTypes.func.isRequired,
  ideaTitle: PropTypes.string,
  isAuthenticated: PropTypes.bool,
  isAuthor: PropTypes.bool,
  isSaving: PropTypes.bool,
  onBackClicked: PropTypes.func.isRequired,
  onDeleteClicked: PropTypes.func.isRequired,
  onPublishClicked: PropTypes.func.isRequired,
  onReportClicked: PropTypes.func,
  onSaveClicked: PropTypes.func.isRequired,
  showContent: PropTypes.bool,
  showSaveBanner: PropTypes.bool,
  status: PropTypes.string,
  toggleReadingMode: PropTypes.func.isRequired
};

export default IdeaPageHeader;
