import React from 'react';
import PropTypes from 'prop-types';
import Switch from '../../components/Switch';
import IdeaReader from '../../components/IdeaReader';
import CreateIdeaActions from './CreateIdeaActions';
import IdeaPageTitle from './IdeaPageTitle';
import CreateIdeaTool from './CreateIdeaTool';
import { FIRST_QUESTIONS, SECOND_QUESTIONS } from './constants/questions';

function getInitialState(questions = []) {
    return questions.reduce((acc, question) => {
        acc[question.id] = '';
        return acc;
    }, {});
}

class CreateIdeaPage extends React.Component {
    constructor(props) {
        super(props);
        const values = { name: '', ...getInitialState(FIRST_QUESTIONS), ...getInitialState(SECOND_QUESTIONS) };
        this.state = { values, readingMode: false };
        this.onQuestionTextChange = this.onQuestionTextChange.bind(this);
        this.onSaveIdea = this.onSaveIdea.bind(this);
        this.onToggleReadingMode = this.onToggleReadingMode.bind(this);
        this.getParagraphs = this.getParagraphs.bind(this);
    }

    onQuestionTextChange(id, value) {
        this.setState(prevState => ({ values: { ...prevState.values, [id]: value } }));
    }

    onToggleReadingMode(toggleValue) {
        this.setState({ readingMode: toggleValue });
    }

    onSaveIdea() {
        const { values } = this.state;
        // TODO: format data before sending them
        this.props.onSaveIdea(values);
    }

    getParagraphs() {
        const questions = [...FIRST_QUESTIONS, ...SECOND_QUESTIONS];
        return questions.reduce((acc, { id }) => {
            if (this.state.values[id]) {
                acc.push(this.state.values[id]);
            }
            return acc;
        }, []);
    }

    render() {
        return (
            <div className="create-idea-page">
                <div className="create-idea-page__header l__wrapper">
                    <button className="button create-idea-actions__back" onClick={() => this.props.onBackClicked()}>
                        ← Retour
                    </button>
                    <Switch onChange={this.onToggleReadingMode} label="Passer en mode lecture" />
                    {this.props.isAuthor && (
                        <CreateIdeaActions
                            onDeleteClicked={this.props.onDeleteClicked}
                            onPublishClicked={() => this.props.onPublishClicked(this.state)}
                            onSaveClicked={this.onSaveIdea}
                            mode="header"
                        />
                    )}
                </div>
                <div className="create-idea-page__content">
                    <div className="create-idea-page__content__main l__wrapper--medium">
                        <IdeaPageTitle
                            authorName={this.props.metadata.authorName}
                            createdAt={this.props.metadata.createdAt}
                            onTitleChange={value => this.onQuestionTextChange('name', value)}
                            title={this.state.values.name}
                            isEditing={this.props.isEditing && !this.state.readingMode}
                        />
                        {this.state.readingMode ? (
                            <IdeaReader paragraphs={this.getParagraphs()} />
                        ) : (
                            <CreateIdeaTool
                                onQuestionTextChange={this.onQuestionTextChange}
                                values={this.state.values}
                                isEditing={this.props.isEditing}
                            />
                        )}
                        <div className="create-idea-page__footer">
                            {this.props.isAuthor && !this.state.readingMode && (
                                <CreateIdeaActions
                                    onDeleteClicked={this.props.onDeleteClicked}
                                    onPublishClicked={() => this.props.onPublishClicked(this.state)}
                                    onSaveClicked={this.onSaveIdea}
                                />
                            )}
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

CreateIdeaPage.defaultProps = {
    isAuthor: false,
    metadata: {},
    isEditing: false,
};

CreateIdeaPage.propTypes = {
    isAuthor: PropTypes.bool,
    metadata: PropTypes.shape({ authorName: PropTypes.string.isRequired, createdAt: PropTypes.string }),
    isEditing: PropTypes.bool,
    onBackClicked: PropTypes.func.isRequired,
    onPublishClicked: PropTypes.func.isRequired,
    onDeleteClicked: PropTypes.func.isRequired,
    onSaveIdea: PropTypes.func.isRequired,
};

export default CreateIdeaPage;
