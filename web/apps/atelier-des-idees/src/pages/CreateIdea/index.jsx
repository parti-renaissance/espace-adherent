import React from 'react';
import PropTypes from 'prop-types';
import Switch from '../../components/Switch';
import IdeaReader from '../../components/IdeaReader';
import CreateIdeaActions from './CreateIdeaActions';
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
        const values = { title: '', ...getInitialState(FIRST_QUESTIONS), ...getInitialState(SECOND_QUESTIONS) };
        this.state = { values, readingMode: false };
        this.onQuestionTextChange = this.onQuestionTextChange.bind(this);
        this.onToggleReadingMode = this.onToggleReadingMode.bind(this);
        this.getParagraphs = this.getParagraphs.bind(this);
    }

    onQuestionTextChange(id, value) {
        this.setState(prevState => ({ values: { ...prevState.values, [id]: value } }));
    }

    onToggleReadingMode(toggleValue) {
        this.setState({ readingMode: toggleValue });
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
                            onPublishClicked={() => this.props.onPublichClicked(this.state)}
                            onSaveClicked={this.props.onSaveClicked}
                            mode="header"
                        />
                    )}
                </div>
                <div className="create-idea-page__content">
                    <div className="create-idea-page__content__main l__wrapper--medium">
                        {/* TODO: add authorName and createdAt props */}
                        {this.state.readingMode ? (
                            <IdeaReader title={this.state.values.title} paragraphs={this.getParagraphs()} />
                        ) : (
                            <CreateIdeaTool
                                onQuestionTextChange={this.onQuestionTextChange}
                                values={this.state.values}
                            />
                        )}
                        <div className="create-idea-page__footer">
                            {this.props.isAuthor && !this.state.readingMode && (
                                <CreateIdeaActions
                                    onDeleteClicked={this.props.onDeleteClicked}
                                    onPublishClicked={() => this.props.onPublichClicked(this.state)}
                                    onSaveClicked={this.props.onSaveClicked}
                                />
                            )}
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

// TODO: remove default props when linking to proper callbacks
CreateIdeaPage.defaultProps = {
    isAuthor: true,
    onBackClicked: () => alert('Retour'),
    onPublichClicked: () => alert('Publier'),
    onDeleteClicked: () => alert('Supprimer'),
    onSaveClicked: () => alert('Enregistrer'),
};

CreateIdeaPage.propTypes = {
    isAuthor: PropTypes.bool,
    onBackClicked: PropTypes.func.isRequired,
    onPublichClicked: PropTypes.func.isRequired,
    onDeleteClicked: PropTypes.func.isRequired,
    onSaveClicked: PropTypes.func.isRequired,
};

export default CreateIdeaPage;
