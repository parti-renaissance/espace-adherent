import React from 'react';
import PropTypes from 'prop-types';
import queryString from 'query-string';
import { ideaStatus } from '../../constants/api';
import CreateIdeaActions from './CreateIdeaActions';
import IdeaPageHeader from './IdeaPageHeader';
import IdeaPageTitle from './IdeaPageTitle';
import IdeaContent from './IdeaContent';
import VotingFooterIdeaPage from './VotingFooterIdeaPage';
import IdeaPageSkeleton from './IdeaPageSkeleton';
import autoSaveIcn from '../../img/icn_20px_autosave.svg';
import greenCheckIcn from '../../img/icn_checklist.svg';
import greenHourglassIcn from '../../img/icn_hourglass_green.svg';

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
        const { mode } = queryString.parse(props.location.search);
        this.requiredQuestions = getRequiredAnswers(props.guidelines);
        this.state = {
            name: props.idea.name || '',
            answers,
            errors: {
                name: false,
            },
            readingMode: props.idea.status === ideaStatus.FINALIZED || 'lecture' === mode,
            showSaveBanner: false,
        };
        this.saveBannerTimer = null;
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
        this.setState({ readingMode: toggleValue }, () => {
            window.scrollTo(0, 0);
        });
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

    componentDidUpdate(prevProps) {
        if (
            this.props.idea.status === ideaStatus.DRAFT &&
            prevProps.isSaveSuccess !== this.props.isSaveSuccess &&
            this.props.isSaveSuccess
        ) {
            // idea save is successful, notify user (only in draft mode)
            clearTimeout(this.saveBannerTimer);
            this.setState({ showSaveBanner: true });
            // remove the banner after 5 secs
            this.saveBannerTimer = setTimeout(() => {
                this.setState({
                    showSaveBanner: false,
                });
            }, 5000);
        }
    }

    componentWillUnmount() {
        clearTimeout(this.saveBannerTimer);
    }

    render() {
        const { idea } = this.props;
        return (
            <div className="idea-page">
                <IdeaPageHeader
                    canToggleReadingMode={idea.status !== ideaStatus.FINALIZED && !!this.state.name}
                    closeSaveBanner={() => {
                        this.setState({ showSaveBanner: false }, () => {
                            clearTimeout(this.saveBannerTimer);
                        });
                    }}
                    ideaTitle={idea.name}
                    isAuthenticated={this.props.isAuthenticated}
                    isAuthor={this.props.isAuthor}
                    isSaving={this.props.isSaving}
                    onBackClicked={this.props.onBackClicked}
                    onDeleteClicked={this.props.onDeleteClicked}
                    onPublishClicked={this.onPublishIdea}
                    onReportClicked={this.props.onReportClicked}
                    onSaveClicked={this.onSaveIdea}
                    showContent={!this.props.isLoading}
                    showSaveBanner={this.state.showSaveBanner}
                    status={idea.status}
                    toggleReadingMode={this.onToggleReadingMode}
                    isReading={this.state.readingMode}
                />
                <div className="idea-page__content">
                    {!this.props.isLoading && idea.status === ideaStatus.DRAFT && (
                        <div className="idea-page__auto-save">
                            <p className="idea-page__auto-save__label">
                                <img className="idea-page__auto-save__icon" src={autoSaveIcn} />
                                <span>Votre contenu sera sauvegardé toutes les minutes</span>
                            </p>
                        </div>
                    )}
                    <div className="idea-page__content__main l__wrapper--medium">
                        {this.props.isLoading ? (
                            <IdeaPageSkeleton />
                        ) : (
                            <React.Fragment>
                                {idea.status !== ideaStatus.DRAFT && (
                                    <p className="idea-page__status-label">
                                        <img
                                            className="idea-page__status-label__icn"
                                            src={idea.status === ideaStatus.PENDING ? greenHourglassIcn : greenCheckIcn}
                                        />
                                        <span>
                                            {idea.status === ideaStatus.PENDING
                                                ? 'Proposition en cours'
                                                : 'Proposition finalisée'}
                                        </span>
                                    </p>
                                )}
                                <IdeaPageTitle
                                    themes={idea.themes}
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
                                <IdeaContent
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
                                    fullAnswers={idea.answers}
                                />
                                {idea.status === ideaStatus.DRAFT && (
                                    <div className="idea-page__footer">
                                        {this.props.isAuthor && !this.state.readingMode && (
                                            <CreateIdeaActions
                                                onDeleteClicked={this.props.onDeleteClicked}
                                                onPublishClicked={this.onPublishIdea}
                                                onSaveClicked={this.onSaveIdea}
                                                isDraft={true}
                                                isSaving={this.props.isSaving}
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
    isSaving: false,
    isSaveSuccess: false,
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
        themes: PropTypes.arrayOf(
            PropTypes.shape({
                id: PropTypes.number,
                name: PropTypes.string,
                thumbnail: PropTypes.string,
            })
        ),
        status: PropTypes.oneOf(Object.keys(ideaStatus)),
        published_at: PropTypes.string,
    }),
    guidelines: PropTypes.array.isRequired,
    isAuthor: PropTypes.bool,
    isAuthenticated: PropTypes.bool,
    isLoading: PropTypes.bool,
    isSaving: PropTypes.bool,
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
    isSaveSuccess: PropTypes.bool,
};

export default IdeaPageBase;
