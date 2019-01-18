import React from 'react';
import PropTypes from 'prop-types';
import { ideaStatus } from '../../constants/api';
import Switch from '../../components/Switch';
import IdeaReader from '../../components/IdeaReader';
import CreateIdeaActions from './CreateIdeaActions';
import IdeaPageTitle from './IdeaPageTitle';
import CreateIdeaTool from './CreateIdeaTool';
import VotingFooterIdeaPage from './VotingFooterIdeaPage';
import IdeaPageSkeleton from './IdeaPageSkeleton';
import autoSaveIcn from '../../img/icn_20px_autosave.svg';

function getInitialAnswers(guidelines, answers = []) {
    const questions = guidelines.reduce((acc, guideline) => [...acc, ...guideline.questions], []);
    return questions.reduce((acc, question) => {
        const answer = answers.find(item => item.question.id === question.id);
        acc[question.id] = answer ? answer.content : '';
        return acc;
    }, {});
}

function getRequiredAnswers(guidelines) {
    const questions = guidelines.reduce((acc, guideline) => [...acc, ...guideline.questions], []);
    return questions
        .filter(question => question.required)
        .reduce((acc, question) => {
            acc[question.id] = false;
            return acc;
        }, {});
}

class IdeaPageBase extends React.Component {
    constructor(props) {
        super(props);
        // init state
        const answers = getInitialAnswers(props.guidelines, props.idea.answers);
        const requiredAnswers = getRequiredAnswers(props.guidelines);
        this.state = {
            name: props.idea.name || '',
            answers,
            errors: {
                name: false,
                ...requiredAnswers,
            },
            readingMode: props.idea.status === ideaStatus.FINALIZED,
        };
        // bindings
        this.onNameChange = this.onNameChange.bind(this);
        this.onQuestionTextChange = this.onQuestionTextChange.bind(this);
        this.onSaveIdea = this.onSaveIdea.bind(this);
        this.onPublishIdea = this.onPublishIdea.bind(this);
        this.onToggleReadingMode = this.onToggleReadingMode.bind(this);
        this.getParagraphs = this.getParagraphs.bind(this);
        this.formatAnswers = this.formatAnswers.bind(this);
    }

    onNameChange(value, withSave = false) {
        this.setState(
            prevState => ({
                name: value,
                errors: { ...prevState.errors, name: !value },
            }),
            () => {
                if (value && withSave) {
                    this.onSaveIdea();
                }
            }
        );
    }

    onQuestionTextChange(id, value, withSave = false) {
        this.setState(
            prevState => ({
                answers: { ...prevState.answers, [id]: value },
            }),
            () => {
                // check if field is required and set errors accordingly
                const isFieldRequired = Object.keys(this.state.errors).includes(id.toString());
                this.setState((prevState) => {
                    const errors = { ...prevState.errors };
                    if (isFieldRequired && errors[id]) {
                        // if is required and already in error, update error state
                        errors[id] = !value;
                    }
                    return {
                        errors,
                    };
                });
                if (withSave) {
                    this.onSaveIdea();
                }
            }
        );
    }

    onToggleReadingMode(toggleValue) {
        this.setState({ readingMode: toggleValue });
    }

    formatAnswers() {
        const formattedAnswers = Object.entries(this.state.answers).map(([id, value], index) => ({
            question: id,
            content: value,
        }));
        return formattedAnswers;
    }

    onSaveIdea() {
        const { name } = this.state;
        if (name) {
            // format data before sending them
            const data = { name, answers: this.formatAnswers() };
            this.props.onSaveIdea(data);
        } else {
            this.setState(prevState => ({
                errors: { ...prevState.errors, name: true },
            }));
        }
    }

    onPublishIdea() {
        if (this.hasRequiredValues()) {
            // format data before sending them
            const data = { name: this.state.name, answers: this.formatAnswers() };
            this.props.onPublishIdea(data);
        }
    }

    getParagraphs() {
        const questions = this.props.guidelines.reduce((acc, guideline) => [...acc, ...guideline.questions], []);
        return questions.reduce((acc, { id }) => {
            if (this.state.answers[id]) {
                acc.push(this.state.answers[id]);
            }
            return acc;
        }, []);
    }

    hasRequiredValues() {
        const { answers, errors } = this.state;
        // check if all the required questions are answered
        const { name, ...answersErrors } = errors;
        // compute missing values from required fields
        const missingValues = Object.keys(answersErrors).reduce((acc, questionId) => {
            if (!answers[questionId]) {
                acc[questionId] = true;
            }
            return acc;
        }, {});
        // update name error
        if (!this.state.name) {
            missingValues.name = true;
        }
        this.setState({ errors: missingValues });
        // check if form has all the required answers
        const hasRequiredAnswers = 0 === Object.keys(missingValues).length;
        // true if has answered to required questions and has set a name
        return hasRequiredAnswers;
    }

    render() {
        const { idea } = this.props;
        return (
            <div className="create-idea-page">
                <section className="header">
                    <div className="create-idea-page__header l__wrapper">
                        {!this.props.isLoading && (
                            <React.Fragment>
                                <button
                                    className="button create-idea-actions__back"
                                    onClick={() => this.props.onBackClicked()}
                                >
                                    ← Retour
                                </button>
                                {idea.status !== ideaStatus.FINALIZED && this.state.name && (
                                    <Switch onChange={this.onToggleReadingMode} label="Passer en mode lecture" />
                                )}
                                {this.props.isAuthor && (
                                    <CreateIdeaActions
                                        onDeleteClicked={this.props.onDeleteClicked}
                                        onPublishClicked={this.onPublishIdea}
                                        onSaveClicked={this.onSaveIdea}
                                        isEditing={idea.status === ideaStatus.DRAFT}
                                    />
                                )}
                                {this.props.isAuthenticated && !this.props.isAuthor && (
                                    <button
                                        className="button create-idea-actions__report"
                                        onClick={() => this.props.onReportClicked()}
                                    >
                                        Signaler la note
                                    </button>
                                )}
                            </React.Fragment>
                        )}
                    </div>
                </section>
                <div className="create-idea-page__content">
                    {!this.props.isLoading && idea.status === ideaStatus.DRAFT && (
                        <div className="create-idea-page__auto-save">
                            <p className="create-idea-page__auto-save__label">
                                <img className="create-idea-page__auto-save__icon" src={autoSaveIcn} />
                                <span>Votre brouillon sera sauvegardé toutes les minutes</span>
                            </p>
                        </div>
                    )}
                    <div className="create-idea-page__content__main l__wrapper--medium">
                        {this.props.isLoading ? (
                            <IdeaPageSkeleton />
                        ) : (
                            <React.Fragment>
                                <IdeaPageTitle
                                    authorName={idea.authorName}
                                    publishedAt={idea.publishedAt}
                                    onTitleChange={(value, withSave) => this.onNameChange(value, withSave)}
                                    title={this.state.name}
                                    isAuthor={this.props.isAuthor}
                                    isEditing={idea.status === ideaStatus.DRAFT}
                                    isReadOnly={this.state.readingMode || !this.props.isAuthor}
                                    hasError={this.state.errors.name}
                                    showPublicationDate={idea.status === ideaStatus.FINALIZED}
                                />
                                {this.state.readingMode ? (
                                    <IdeaReader paragraphs={this.getParagraphs()} />
                                ) : (
                                    <CreateIdeaTool
                                        onQuestionTextChange={this.onQuestionTextChange}
                                        guidelines={this.props.guidelines}
                                        values={this.state.answers}
                                        isAuthor={this.props.isAuthor}
                                        isDraft={idea.status === ideaStatus.DRAFT}
                                        onAutoSave={this.onSaveIdea}
                                        errors={Object.entries(this.state.errors)
                                            .filter(([, hasError]) => hasError)
                                            .map(([key]) => key)}
                                    />
                                )}
                                {idea.status === ideaStatus.DRAFT && (
                                    <div className="create-idea-page__footer">
                                        {this.props.isAuthor && !this.state.readingMode && (
                                            <CreateIdeaActions
                                                onDeleteClicked={this.props.onDeleteClicked}
                                                onPublishClicked={this.onPublishIdea}
                                                onSaveClicked={this.onSaveIdea}
                                            />
                                        )}
                                    </div>
                                )}
                                {idea.status === ideaStatus.FINALIZED && <VotingFooterIdeaPage />}
                            </React.Fragment>
                        )}
                    </div>
                </div>
            </div>
        );
    }
}

IdeaPageBase.defaultProps = {
    idea: {},
    isAuthor: false,
    isAuthenticated: false,
    isLoading: false,
};

IdeaPageBase.propTypes = {
    idea: PropTypes.shape({
        name: PropTypes.string,
        answers: PropTypes.arrayOf(
            PropTypes.shape({
                id: PropTypes.string,
                content: PropTypes.string,
                question: PropTypes.shape({ id: PropTypes.number }),
            })
        ),
        status: PropTypes.oneOf(Object.keys(ideaStatus)),
        publishedAt: PropTypes.string,
    }),
    guidelines: PropTypes.array.isRequired,
    isAuthor: PropTypes.bool,
    isAuthenticated: PropTypes.bool,
    isLoading: PropTypes.bool,
    onBackClicked: PropTypes.func.isRequired,
    onPublishIdea: PropTypes.func.isRequired,
    onDeleteClicked: PropTypes.func.isRequired,
    onReportClicked: PropTypes.func.isRequired,
    onSaveIdea: PropTypes.func.isRequired,
};

export default IdeaPageBase;
