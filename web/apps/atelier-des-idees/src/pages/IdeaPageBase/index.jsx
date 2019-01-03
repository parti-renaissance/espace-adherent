import React from 'react';
import PropTypes from 'prop-types';
import { ideaStatus } from '../../constants/api';
import Switch from '../../components/Switch';
import IdeaReader from '../../components/IdeaReader';
import CreateIdeaActions from './CreateIdeaActions';
import IdeaPageTitle from './IdeaPageTitle';
import CreateIdeaTool from './CreateIdeaTool';

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
        this.onToggleReadingMode = this.onToggleReadingMode.bind(this);
        this.getParagraphs = this.getParagraphs.bind(this);
        this.formatAnswers = this.formatAnswers.bind(this);
    }

    onNameChange(value) {
        this.setState(prevState => ({ name: value, errors: { ...prevState.errors, name: !value } }));
    }

    onQuestionTextChange(id, value) {
        this.setState(
            prevState => ({
                answers: { ...prevState.answers, [id]: value },
            }),
            () => this.setState(prevState => ({ errors: { ...prevState.errors, name: !this.state.name } }))
        );
    }

    onToggleReadingMode(toggleValue) {
        this.setState({ readingMode: toggleValue });
    }

    formatAnswers() {
        const formattedAnswers = Object.entries(this.state.answers)
            .filter(([, value]) => !!value)
            .map(([id, value], index) => {
                if (value) {
                    return { question: id, content: value };
                }
                return null;
            });
        return formattedAnswers;
    }

    onSaveIdea() {
        const { name } = this.state;
        if (name) {
            // format data before sending them
            const data = { name, answers: this.formatAnswers() };
            this.props.onSaveIdea(data);
        } else {
            this.setState(prevState => ({ errors: { ...prevState.errors, name: true } }));
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
        const hasRequiredAnswers = Object.keys(answersErrors).reduce(
            (acc, questionId) => acc && !!answers[questionId],
            true
        );
        // true if has answered to required questions and has set a name
        return hasRequiredAnswers && !!this.state.name;
    }

    render() {
        const { idea } = this.props;
        return (
            <div className="create-idea-page">
                <div className="create-idea-page__header l__wrapper">
                    <button className="button create-idea-actions__back" onClick={() => this.props.onBackClicked()}>
                        ← Retour
                    </button>
                    {idea.status !== ideaStatus.FINALIZED && this.state.name && (
                        <Switch onChange={this.onToggleReadingMode} label="Passer en mode lecture" />
                    )}
                    {this.props.isAuthor && (
                        <CreateIdeaActions
                            onDeleteClicked={this.props.onDeleteClicked}
                            onPublishClicked={() => this.props.onPublishClicked(this.state)}
                            onSaveClicked={this.onSaveIdea}
                            isEditing={idea.status === ideaStatus.DRAFT}
                            canPublish={this.hasRequiredValues()}
                        />
                    )}
                </div>
                <div className="create-idea-page__content">
                    <div className="create-idea-page__content__main l__wrapper--medium">
                        <IdeaPageTitle
                            authorName={this.props.idea.authorName}
                            createdAt={this.props.idea.createdAt}
                            onTitleChange={value => this.onNameChange(value)}
                            title={this.state.name}
                            isEditing={idea.status === ideaStatus.DRAFT && !this.state.readingMode}
                            hasError={this.state.errors.name}
                        />
                        {this.state.readingMode ? (
                            <IdeaReader paragraphs={this.getParagraphs()} />
                        ) : (
                            <CreateIdeaTool
                                onQuestionTextChange={this.onQuestionTextChange}
                                guidelines={this.props.guidelines}
                                values={this.state.answers}
                                isEditing={idea.status === ideaStatus.DRAFT}
                            />
                        )}
                        {idea.status === ideaStatus.DRAFT && (
                            <div className="create-idea-page__footer">
                                {this.props.isAuthor && !this.state.readingMode && (
                                    <CreateIdeaActions
                                        onDeleteClicked={this.props.onDeleteClicked}
                                        onPublishClicked={() => this.props.onPublishClicked(this.state)}
                                        onSaveClicked={this.onSaveIdea}
                                        canPublish={this.hasRequiredValues()}
                                    />
                                )}
                            </div>
                        )}
                        {/* TODO: add voting footer for FINALIZED status */}
                    </div>
                </div>
            </div>
        );
    }
}

IdeaPageBase.defaultProps = {
    idea: {},
    isAuthor: false,
};

IdeaPageBase.propTypes = {
    idea: PropTypes.shape({
        name: PropTypes.string,
        answers: PropTypes.arrayOf(
            PropTypes.shape({
                content: PropTypes.string,
                question: PropTypes.shape({ id: PropTypes.number }),
            })
        ),
        status: PropTypes.oneOf(Object.keys(ideaStatus)),
    }),
    guidelines: PropTypes.array.isRequired,
    isAuthor: PropTypes.bool,
    onBackClicked: PropTypes.func.isRequired,
    onPublishClicked: PropTypes.func.isRequired,
    onDeleteClicked: PropTypes.func.isRequired,
    onSaveIdea: PropTypes.func.isRequired,
};

export default IdeaPageBase;
