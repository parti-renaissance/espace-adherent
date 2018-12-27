import React from 'react';
import PropTypes from 'prop-types';
import QuestionBlock from './QuestionBlock';
import CreateIdeaActions from './CreateIdeaActions';

const FIRST_QUESTIONS = [
    {
        id: 'problem',
        label: 'Constat',
        question: 'Quel problème souhaitez vous résoudre ?',
        canCollapse: false,
        placeholder: 'Expliquez le problème que vous identifiez et espérez pouvoir remédier.',
    },
    {
        id: 'solution',
        label: 'Solution',
        question: 'Quelle réponse votre idée apporte-t-elle ?',
        canCollapse: false,
        placeholder: 'Expliquez comment votre proposition répond concrètement au problème.',
    },
    {
        id: 'comparison',
        label: 'Comparaison',
        question: 'Cette proposition a-t-elle déjà été mise en oeuvre ou étudiée ?',
        canCollapse: true,
    },
    {
        id: 'impact',
        label: 'Impact',
        question: 'Cette proposition peut elle avoir des effets négatifs pour certains publics ?',
        canCollapse: true,
    },
];

const SECOND_QUESTIONS = [
    {
        id: 'right',
        label: 'Droit',
        question: 'Votre idée suppose t-elle de changer le droit ?',
        canCollapse: false,
        placeholder:
            'Expliquez si votre idée nécessite - ou non - de changer le droit en vigueur. Si oui, idéalement, précisez ce qu’il faudrait changer.',
    },
    {
        id: 'budget',
        label: 'Budget',
        question: 'Votre idée a-t-elle un impact financier ?',
        canCollapse: true,
    },
    {
        id: 'environment',
        label: 'Environnement',
        question: 'Votre idée a t-elle un impact écologique ?',
        canCollapse: true,
    },
    {
        id: 'parity',
        label: 'Égalité hommes-femmes',
        question: 'Votre idée a t-elle un impact sur l’égalité entre les femmes et les hommes ?',
        canCollapse: true,
    },
];

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
                    <CreateIdeaActions
                        onDeleteClicked={this.props.onDeleteClicked}
                        onPublishClicked={() => this.props.onPublichClicked(this.state)}
                        onSaveClicked={this.props.onSaveClicked}
                        mode="header"
                    />
                </div>
                <div className="create-idea-page__content">
                    <div className="create-idea-page__content__main l__wrapper--medium">
                        <section className="create-idea-page__title-section">
                            {/* TODO: replace with TextArea component */}
                            <textarea
                                value={this.state.title}
                                onChange={e => this.setState({ title: e.target.value })}
                                placeholder="Titre de l'idée"
                            />
                        </section>
                        <section className="create-idea-page__start-section">
                            <h2>Quelles sont les caractéristiques principales de votre idée ?</h2>
                            {FIRST_QUESTIONS.map(({ id, label, question, placeholder, canCollapse }, index) => (
                                <QuestionBlock
                                    canCollapse={canCollapse}
                                    key={id}
                                    label={label}
                                    question={question}
                                    placeholder={placeholder}
                                    nbQuestion={index + 1}
                                    onTextChange={htmlContent => this.onQuestionTextChange(id, htmlContent)}
                                />
                            ))}
                        </section>
                        <section className="create-idea-page__continue-section">
                            <h2>Votre idée peut-elle être mise en oeuvre ?</h2>
                            {SECOND_QUESTIONS.map(({ id, label, question, placeholder, canCollapse }, index) => (
                                <QuestionBlock
                                    canCollapse={canCollapse}
                                    key={id}
                                    label={label}
                                    question={question}
                                    placeholder={placeholder}
                                    nbQuestion={FIRST_QUESTIONS.length + index + 1}
                                    onTextChange={htmlContent => this.onQuestionTextChange(id, htmlContent)}
                                />
                            ))}
                        </section>
                        <div className="create-idea-page__footer">
                            <CreateIdeaActions
                                onDeleteClicked={this.props.onDeleteClicked}
                                onPublishClicked={() => this.props.onPublichClicked(this.state)}
                                onSaveClicked={this.props.onSaveClicked}
                                mode="footer"
                            />
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

CreateIdeaPage.propTypes = {
    onBackClicked: PropTypes.func.isRequired,
    onPublichClicked: PropTypes.func.isRequired,
    onDeleteClicked: PropTypes.func.isRequired,
    onSaveClicked: PropTypes.func.isRequired,
};

export default CreateIdeaPage;
