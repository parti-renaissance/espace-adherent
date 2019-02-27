import React from 'react';
import PropTypes from 'prop-types';
import TextArea from '../../../components/TextArea';

/**
 * This component handles both DRAFT and PENDING status
 * It is controlled in the first case and uncontrolled in the second one (using state.isEditing)
 */
class IdeaPageTitle extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            value: props.title,
            isEditing: false,
        };
        this.onTitleChange = this.onTitleChange.bind(this);
    }

    onTitleChange(value) {
        if (this.state.isEditing) {
            this.setState({ value });
        } else {
            this.props.onTitleChange(value);
        }
    }

    render() {
        return (
            <section className="idea-page-title">
                {!this.props.isReadOnly && (this.props.isEditing || this.state.isEditing) ? (
                    <React.Fragment>
                        <TextArea
                            maxLength={120}
                            onChange={this.onTitleChange}
                            placeholder="Titre de votre proposition"
                            value={this.state.isEditing ? this.state.value : this.props.title}
                            error={
                                this.props.hasError
                                    ? 'Merci de remplir 15 caractères minimum avant de poursuivre'
                                    : undefined
                            }
                            name="title"
                        />
                        {this.state.isEditing && (
                            <div className="idea-page-title__title__editing-footer">
                                <button
                                    className="idea-page-title__title__editing-footer__btn"
                                    onClick={() => this.setState({ isEditing: false, value: this.props.title })}
                                >
									Annuler
                                </button>
                                {this.state.value.length >= this.props.minLength && (
                                    <button
                                        className="idea-page-title__title__editing-footer__btn editing-footer__btn--save"
                                        onClick={() => {
                                            this.props.onTitleChange(this.state.value, true);
                                            this.setState({ isEditing: false });
                                        }}
                                    >
										Enregistrer
                                    </button>
                                )}
                            </div>
                        )}
                    </React.Fragment>
                ) : (
                    <React.Fragment>
                        <h1 className="idea-page-title__title">
                            {this.props.title}
                            {!this.props.isReadOnly && !this.props.isEditing && this.props.isAuthor && (
                                <button
                                    className="idea-page-title__title__editing-footer__btn editing-footer__btn--edit"
                                    onClick={() => this.setState({ isEditing: true })}
                                >
									Editer
                                </button>
                            )}
                        </h1>
                        <div className="idea-page-title__info">
                            {this.props.authorName && (
                                <span className="idea-page-title__info__author">
									Par{' '}
                                    <span className="idea-page-title__info__author-name">{this.props.authorName}</span>
                                </span>
                            )}
                            {this.props.showPublicationDate && this.props.publishedAt && (
                                <span className="idea-page-title__info__date">
                                    {new Date(this.props.publishedAt).toLocaleDateString()}
                                </span>
                            )}
                            <ul>
                                {this.props.themes &&
									this.props.themes.map(theme => (
									    <li className="idea-page-title__tags__item">{theme.name}</li>
									))}
                            </ul>
                        </div>
                    </React.Fragment>
                )}
            </section>
        );
    }
}

IdeaPageTitle.defaultProps = {
    authorName: '',
    publishedAt: '',
    hasError: false,
    isAuthor: false,
    isEditing: false,
    isReadOnly: true,
    showPublicationDate: false,
    minLength: 0,
};

IdeaPageTitle.propTypes = {
    authorName: PropTypes.string,
    publishedAt: PropTypes.string,
    hasError: PropTypes.bool,
    onTitleChange: PropTypes.func.isRequired,
    isAuthor: PropTypes.bool,
    isEditing: PropTypes.bool,
    isReadOnly: PropTypes.bool,
    minLength: PropTypes.number,
    title: PropTypes.string.isRequired,
    showPublicationDate: PropTypes.bool,
};

export default IdeaPageTitle;
