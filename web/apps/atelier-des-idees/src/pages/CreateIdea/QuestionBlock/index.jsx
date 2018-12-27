import React from 'react';
import PropTypes from 'prop-types';
import TextEditor from '../../../components/TextEditor';

function QuestionBlockHeader({ label, question, nbQuestion }) {
    return (
        <h3 className="question-block-header">
            <span className="question-block-header__label">{`${nbQuestion}. ${label} : `}</span>
            <span className="question-block-header__question">{question}</span>
        </h3>
    );
}

function QuestionBlock(props) {
    const { label, question, placeholder, nbQuestion, onTextChange, initialContent } = props;
    return (
        <div className="question-block">
            <QuestionBlockHeader label={label} question={question} nbQuestion={nbQuestion} />
            <TextEditor
                initialContent={initialContent}
                maxLength={1700}
                onChange={htmlContent => onTextChange(htmlContent)}
                placeholder={placeholder}
            />
        </div>
    );
}

QuestionBlock.defaultProps = {
    initialContent: '',
    placeholder: undefined,
};

QuestionBlock.propTypes = {
    initialContent: PropTypes.string,
    label: PropTypes.string.isRequired,
    nbQuestion: PropTypes.number.isRequired,
    onTextChange: PropTypes.func.isRequired,
    placeholder: PropTypes.string,
    question: PropTypes.string.isRequired,
};

export default QuestionBlock;
