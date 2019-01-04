import React from 'react';
import PropTypes from 'prop-types';
import TextArea from '../../../components/TextArea';

class IdeaPageTitle extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            value: props.title,
        };
        this.onTitleChange = this.onTitleChange.bind(this);
    }

    onTitleChange(value) {
        this.setState({ value });
    }

    render() {
        return (
            <section className="idea-page-title">
                {this.props.isEditing || this.state.isEditing ? (
                    <React.Fragment>
                        <TextArea
                            maxLength={120}
                            onChange={this.state.isEditing ? this.onTitleChange : this.props.onTitleChange}
                            placeholder="Titre de l'idée"
                            value={this.state.isEditing ? this.state.value : this.props.title}
                            error={this.props.hasError ? 'Merci de remplir un titre avant de poursuivre' : undefined}
                        />
                        {this.state.isEditing && (
                            <div className="idea-page-title__title__editing-footer">
                                <button
                                    className="idea-page-title__title__editing-footer__btn"
                                    onClick={() => this.setState({ isEditing: false, value: this.props.title })}
                                >
                                    Annuler
                                </button>
                                {this.state.value && (
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
                            {this.props.createdAt && (
                                <span className="idea-page-title__info__date"> le {this.props.createdAt}</span>
                            )}
                        </div>
                    </React.Fragment>
                )}
            </section>
        );
    }
}

IdeaPageTitle.defaultProps = {
    authorName: '',
    createdAt: '',
    hasError: false,
    isAuthor: false,
    isEditing: false,
    isReadOnly: true,
};

IdeaPageTitle.propTypes = {
    authorName: PropTypes.string,
    createdAt: PropTypes.string,
    hasError: PropTypes.bool,
    onTitleChange: PropTypes.func.isRequired,
    isAuthor: PropTypes.bool,
    isEditing: PropTypes.bool,
    isReadOnly: PropTypes.bool,
    title: PropTypes.string.isRequired,
};

export default IdeaPageTitle;
