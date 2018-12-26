import React from 'react';
import PropTypes from 'prop-types';
import TextEditor from '../../components/TextEditor';

const FIRST_QUESTIONS = [
    {
        label: 'Constat',
        question: 'Quel problème souhaitez vous résoudre ?',
        canCollapse: false,
        placeholder: 'Expliquez le problème que vous identifiez et espérez pouvoir remédier.',
    },
    {
        label: 'Solution',
        question: 'Quelle réponse votre idée apporte-t-elle ?',
        canCollapse: false,
        placeholder: 'Expliquez comment votre proposition répond concrètement au problème.',
    },
    {
        label: 'Comparaison',
        question: 'Cette proposition a-t-elle déjà été mise en oeuvre ou étudiée ?',
        canCollapse: true,
    },
    {
        label: 'Impact',
        question: 'Cette proposition peut elle avoir des effets négatifs pour certains publics ?',
        canCollapse: true,
    },
];

const SECOND_QUESTIONS = [
    {
        label: 'Droit',
        question: 'Votre idée suppose t-elle de changer le droit ?',
        canCollapse: false,
        placeholder:
            'Expliquez si votre idée nécessite - ou non - de changer le droit en vigueur. Si oui, idéalement, précisez ce qu’il faudrait changer.',
    },
    {
        label: 'Budget',
        question: 'Votre idée a-t-elle un impact financier ?',
        canCollapse: false,
    },
    {
        label: 'Environnement',
        question: 'Votre idée a t-elle un impact écologique ?',
        canCollapse: true,
    },
    {
        label: 'Égalité hommes-femmes',
        question: 'Votre idée a t-elle un impact sur l’égalité entre les femmes et les hommes ?',
        canCollapse: true,
    },
];

function QuestionBlockHeader({ label, question, nbQuestion }) {
    return (
        <h3 className="question-block-header">
            <span className="question-block-header__label">{`${nbQuestion}. ${label} : `}</span>
            <span className="question-block-header__question">{question}</span>
        </h3>
    );
}

function renderQuestionBlock({ label, question, placeholder }, nbQ) {
    return (
        <div className="question-block">
            <QuestionBlockHeader label={label} question={question} nbQuestion={nbQ} />
            <TextEditor
                maxLength={1700}
                onChange={htmlContent => console.warn(htmlContent)}
                placeholder={placeholder}
            />
        </div>
    );
}

function CreateIdeaPage(props) {
    return (
        <div className="create-idea-page">
            <div className="create-idea-page__header l__wrapper">HEADER</div>
            <div className="create-idea-page__content">
                <div className="create-idea-page__content__main l__wrapper--medium">
                    <section className="create-idea-page__start-section">
                        <h2>Quelles sont les caractéristiques principales de votre idée ?</h2>
                        {FIRST_QUESTIONS.map((question, index) => renderQuestionBlock(question, index + 1))}
                    </section>
                    <section className="create-idea-page__continue-section">
                        <h2>Votre idée peut-elle être mise en oeuvre ?</h2>
                        {SECOND_QUESTIONS.map((question, index) =>
                            renderQuestionBlock(question, FIRST_QUESTIONS.length + index + 1)
                        )}
                    </section>
                    <div className="create-idea-page__content__footer">FOOTER</div>
                </div>
            </div>
        </div>
    );
}

export default CreateIdeaPage;
