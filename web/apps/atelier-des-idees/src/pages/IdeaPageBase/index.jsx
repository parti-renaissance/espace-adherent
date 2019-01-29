import React from 'react';
import PropTypes from 'prop-types';
import { ideaStatus } from '../../constants/api';
import Switch from '../../components/Switch';
import CreateIdeaActions from './CreateIdeaActions';
import IdeaPageTitle from './IdeaPageTitle';
import CreateIdeaTool from './CreateIdeaTool';
import VotingFooterIdeaPage from './VotingFooterIdeaPage';
import IdeaPageSkeleton from './IdeaPageSkeleton';
import autoSaveIcn from '../../img/icn_20px_autosave.svg';

const TITLE_MIN_LENGTH = 15;
const ANSWER_MIN_LENGTH = 15;

/**
 * Returns a map (object) containing the answer (possibly empty) for each question
 * @param {array} guidelines Tools guidelines
 * @param {array} answers Initial answers
 */
function getInitialAnswers(guidelines, answers = []) {
    const questions = guidelines.reduce((acc, guideline) => [...acc, ...guideline.questions], []);
    return questions.reduce((acc, question) => {
        const answer = answers.find(item => item.question.id === question.id);
        acc[question.id] = answer ? answer.content : '';
        return acc;
    }, {});
}

/**
 * Returns an array containing required questions ids
 * @param {array} guidelines Tools guidelines
 */
function getRequiredAnswers(guidelines) {
    const questions = guidelines.reduce((acc, guideline) => [...acc, ...guideline.questions], []);
    return questions.filter(question => question.required).map(question => question.id);
}

class IdeaPageBase extends React.Component {
    constructor(props) {
        super(props);
        // init state
        // get required questions and set required errors
        const answers = getInitialAnswers(props.guidelines, props.idea.answers);
        this.requiredQuestions = getRequiredAnswers(props.guidelines);
        this.state = {
            name: props.idea.name || '',
            answers,
            errors: {
                name: false,
            },
            readingMode: props.idea.status === ideaStatus.FINALIZED,
        };
        // bindings
        this.onNameChange = this.onNameChange.bind(this);
        this.onQuestionTextChange = this.onQuestionTextChange.bind(this);
        this.onSaveIdea = this.onSaveIdea.bind(this);
        this.onPublishIdea = this.onPublishIdea.bind(this);
        this.onToggleReadingMode = this.onToggleReadingMode.bind(this);
        this.formatAnswers = this.formatAnswers.bind(this);
    }

    onNameChange(value, withSave = false) {
        this.setState(
            prevState => ({
                name: value,
                errors: {
                    ...prevState.errors,
                    // if in error, remove it when text respects min length
                    name: !value || (prevState.errors.name ? value.length < TITLE_MIN_LENGTH : prevState.errors.name),
                },
            }),
            () => {
                if (!this.state.errors.name && withSave) {
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
                const isRequired = this.requiredQuestions.includes(parseInt(id, 10));
                this.setState((prevState) => {
                    const errors = { ...prevState.errors };
                    if (errors[id]) {
                        // if is already in error, update error state
                        errors[id] = isRequired
                            ? value.length < ANSWER_MIN_LENGTH
                            : 0 < value.length && value.length < ANSWER_MIN_LENGTH;
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
        const formattedAnswers = Object.entries(this.state.answers).map(([id, value]) => ({
            question: id,
            content: value,
        }));
        return formattedAnswers;
    }

    onSaveIdea() {
        const { name } = this.state;
        if (this.hasCorrectValues(false)) {
            // format data before sending them
            const data = { name, answers: this.formatAnswers() };
            this.props.onSaveIdea(data);
        }
    }

    onPublishIdea() {
        if (this.hasCorrectValues()) {
            // format data before sending them
            const data = { name: this.state.name, answers: this.formatAnswers() };
            this.props.onPublishIdea(data);
        }
    }

    hasCorrectValues(withRequired = true) {
        const { answers } = this.state;
        // check if all the required questions have an answer
        const missingValues = Object.entries(answers).reduce((acc, [questionId, answer]) => {
            const isRequired = withRequired && this.requiredQuestions.includes(parseInt(questionId, 10));
            if (
                // if an answer is required, it must have at least ANSWER_MIN_LENGTH characters
                (isRequired && answer.length < ANSWER_MIN_LENGTH) ||
                // otherwise it can be empty or have at least ANSWER_MIN_LENGTH characters
                (!isRequired && 0 < answer.length && answer.length < ANSWER_MIN_LENGTH)
            ) {
                acc[questionId] = true;
            }
            return acc;
        }, {});
        // update name error
        if (this.state.name.length < TITLE_MIN_LENGTH) {
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
                                        isDraft={idea.status === ideaStatus.DRAFT}
                                        canPublish={
                                            idea.status === ideaStatus.DRAFT || idea.status === ideaStatus.PENDING
                                        }
                                    />
                                )}
                                {this.props.isAuthenticated && !this.props.isAuthor && (
                                    <button
                                        className="button create-idea-actions__report"
                                        onClick={() => this.props.onReportClicked()}
                                    >
                                        Signaler la proposition
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
                                <span>Votre contenu sera sauvegardé toutes les minutes</span>
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
                                    publishedAt={idea.published_at}
                                    onTitleChange={(value, withSave) => this.onNameChange(value, withSave)}
                                    title={this.state.name}
                                    minLength={TITLE_MIN_LENGTH}
                                    isAuthor={this.props.isAuthor}
                                    isEditing={idea.status === ideaStatus.DRAFT}
                                    isReadOnly={this.state.readingMode || !this.props.isAuthor}
                                    hasError={this.state.errors.name}
                                    showPublicationDate={idea.status !== ideaStatus.DRAFT}
                                />
                                <CreateIdeaTool
                                    onQuestionTextChange={this.onQuestionTextChange}
                                    guidelines={this.props.guidelines}
                                    values={this.state.answers}
                                    isAuthor={this.props.isAuthor}
                                    isDraft={idea.status === ideaStatus.DRAFT}
                                    isReading={this.state.readingMode}
                                    onAutoSave={this.onSaveIdea}
                                    errors={Object.entries(this.state.errors)
                                        .filter(([, hasError]) => hasError)
                                        .map(([key]) => key)}
                                />
                                {idea.status === ideaStatus.DRAFT && (
                                    <div className="create-idea-page__footer">
                                        {this.props.isAuthor && !this.state.readingMode && (
                                            <CreateIdeaActions
                                                onDeleteClicked={this.props.onDeleteClicked}
                                                onPublishClicked={this.onPublishIdea}
                                                onSaveClicked={this.onSaveIdea}
                                                isDraft={true}
                                                canPublish={true}
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
                id: PropTypes.number,
                content: PropTypes.string,
                question: PropTypes.shape({ id: PropTypes.number }),
            })
        ),
        status: PropTypes.oneOf(Object.keys(ideaStatus)),
        published_at: PropTypes.string,
    }),
    guidelines: PropTypes.array.isRequired,
    isAuthor: PropTypes.bool,
    isAuthenticated: PropTypes.bool,
    isLoading: PropTypes.bool,
    onBackClicked: PropTypes.func.isRequired,
    onPublishIdea: PropTypes.func.isRequired,
    onDeleteClicked: PropTypes.func.isRequired,
    onReportClicked: (props, propName, componentName) => {
        // onReportClicked required if idea is not a draft (can't report a draft)
        if (!props.onReportClicked && props.idea.status !== ideaStatus.DRAFT) {
            return new Error(`Invalid prop \`${propName}\` supplied to ${componentName}\`. Validation failed.`);
        }
    },
    onSaveIdea: PropTypes.func.isRequired,
};

export default IdeaPageBase;
