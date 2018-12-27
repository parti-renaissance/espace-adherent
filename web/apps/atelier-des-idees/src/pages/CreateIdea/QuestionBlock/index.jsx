import React from 'react';
import PropTypes from 'prop-types';
import TextEditor from '../../../components/TextEditor';
import Collapse from '../../../components/Collapse';

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
            {props.canCollapse ? (
                <Collapse title={<QuestionBlockHeader label={label} question={question} nbQuestion={nbQuestion} />}>
                    <TextEditor
                        initialContent={initialContent}
                        maxLength={1700}
                        onChange={htmlContent => onTextChange(htmlContent)}
                        placeholder={placeholder}
                    />
                </Collapse>
            ) : (
                <React.Fragment>
                    <QuestionBlockHeader label={label} question={question} nbQuestion={nbQuestion} />
                    <TextEditor
                        initialContent={initialContent}
                        maxLength={1700}
                        onChange={htmlContent => onTextChange(htmlContent)}
                        placeholder={placeholder}
                    />
                </React.Fragment>
            )}
        </div>
    );
}

QuestionBlock.defaultProps = {
    canCollapse: false,
    initialContent: '',
    placeholder: undefined,
};

QuestionBlock.propTypes = {
    canCollapse: PropTypes.bool,
    initialContent: PropTypes.string,
    label: PropTypes.string.isRequired,
    nbQuestion: PropTypes.number.isRequired,
    onTextChange: PropTypes.func.isRequired,
    placeholder: PropTypes.string,
    question: PropTypes.string.isRequired,
};

export default QuestionBlock;
