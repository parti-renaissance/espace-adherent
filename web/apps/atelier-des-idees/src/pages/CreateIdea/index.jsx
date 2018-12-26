import React from 'react';
import PropTypes from 'prop-types';

const QUESTIONS = [
    {
        label: 'Constat',
        question: 'Quel problème souhaitez vous résoudre ?',
        canCollapse: false,
    },
    {
        label: 'Solution',
        question: 'Quelle réponse votre idée apporte-t-elle ?',
        canCollapse: false,
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

function QuestionBlockHeader({ label, question, nbQuestion }) {
    return (
        <h3 className="question-block-header">
            <span className="question-block-header__label">{`${nbQuestion}. ${label} : `}</span>
            <span className="question-block-header__question">{question}</span>
        </h3>
    );
}

function renderQuestionBlock({ label, question }, nbQ) {
    return (
        <div className="question-block">
            <QuestionBlockHeader label={label} question={question} nbQuestion={nbQ} />
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
                        {QUESTIONS.map((question, index) => renderQuestionBlock(question, index + 1))}
                    </section>
                    <section className="create-idea-page__continue-section">
                        <h2>Votre idée peut-elle être mise en oeuvre ?</h2>
                    </section>
                    <div className="create-idea-page__content__footer">FOOTER</div>
                </div>
            </div>
        </div>
    );
}

export default CreateIdeaPage;
