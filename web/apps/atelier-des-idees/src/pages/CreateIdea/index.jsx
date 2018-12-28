import React from 'react';
import PropTypes from 'prop-types';
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
        this.state = { title: '', ...getInitialState(FIRST_QUESTIONS), ...getInitialState(SECOND_QUESTIONS) };
        this.onQuestionTextChange = this.onQuestionTextChange.bind(this);
    }

    onQuestionTextChange(id, htmlContent) {
        this.setState({ [id]: htmlContent });
    }

    render() {
        return (
            <div className="create-idea-page">
                <div className="create-idea-page__header l__wrapper">
                    <button className="button create-idea-actions__back" onClick={() => this.props.onBackClicked()}>
                        ← Retour
                    </button>
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
                        <CreateIdeaTool onQuestionTextChange={this.onQuestionTextChange} values={this.state} />
                        <div className="create-idea-page__footer">
                            {this.props.isAuthor && (
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
