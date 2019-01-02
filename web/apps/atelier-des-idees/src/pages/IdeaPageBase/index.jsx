import React from 'react';
import PropTypes from 'prop-types';
import { ideaStatus } from '../../constants/api';
import Switch from '../../components/Switch';
import IdeaReader from '../../components/IdeaReader';
import CreateIdeaActions from './CreateIdeaActions';
import IdeaPageTitle from './IdeaPageTitle';
import CreateIdeaTool from './CreateIdeaTool';
import { FIRST_QUESTIONS, SECOND_QUESTIONS } from './constants/questions';

function getInitialAnswers(answers = []) {
    const questions = [...FIRST_QUESTIONS, ...SECOND_QUESTIONS];
    return questions.reduce((acc, question, index) => {
        const answer = answers.find(item => item.question === index + 1);
        acc[question.id] = answer ? answer.content : '';
        return acc;
    }, {});
}

class IdeaPageBase extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            name: props.idea.name || '',
            answers: getInitialAnswers(props.idea.answers),
            errors: {
                name: false,
            },
            readingMode: props.idea.status === ideaStatus.FINALIZED,
        };
        this.onNameChange = this.onNameChange.bind(this);
        this.onQuestionTextChange = this.onQuestionTextChange.bind(this);
        this.onSaveIdea = this.onSaveIdea.bind(this);
        this.onToggleReadingMode = this.onToggleReadingMode.bind(this);
        this.getParagraphs = this.getParagraphs.bind(this);
        this.formatAnswers = this.formatAnswers.bind(this);
    }

    onNameChange(value) {
        this.setState({ name: value, errors: { name: !value } });
    }

    onQuestionTextChange(id, value) {
        this.setState(
            prevState => ({
                answers: { ...prevState.answers, [id]: value },
            }),
            () => this.setState({ errors: { name: !this.state.name } })
        );
    }

    onToggleReadingMode(toggleValue) {
        this.setState({ readingMode: toggleValue });
    }

    formatAnswers() {
        const formattedAnswers = Object.values(this.state.answers)
            .filter(value => !!value)
            .map((value, index) => {
                if (value) {
                    return { question: index + 1, content: value };
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
            this.setState(prevState => ({ errors: { name: true } }));
        }
    }

    getParagraphs() {
        const questions = [...FIRST_QUESTIONS, ...SECOND_QUESTIONS];
        return questions.reduce((acc, { id }) => {
            if (this.state.answers[id]) {
                acc.push(this.state.answers[id]);
            }
            return acc;
        }, []);
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
        answers: PropTypes.array,
        status: PropTypes.oneOf(Object.keys(ideaStatus)),
    }),
    isAuthor: PropTypes.bool,
    onBackClicked: PropTypes.func.isRequired,
    onPublishClicked: PropTypes.func.isRequired,
    onDeleteClicked: PropTypes.func.isRequired,
    onSaveIdea: PropTypes.func.isRequired,
};

export default IdeaPageBase;
