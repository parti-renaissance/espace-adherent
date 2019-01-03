import React from 'react';
import PropTypes from 'prop-types';
import TextEditor from '../../../components/TextEditor';
import Collapse from '../../../components/Collapse';

function QuestionBlockHeader({ label, question, nbQuestion }) {
    return (
        <h3 className="question-block-header">
            <span className="question-block-header__label">{`${nbQuestion}. ${label ? `${label} : ` : ''}`}</span>
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
                    {'edit' === props.mode ? (
                        <TextEditor
                            initialContent={initialContent}
                            maxLength={1700}
                            onChange={htmlContent => onTextChange(htmlContent)}
                            placeholder={placeholder}
                        />
                    ) : (
                        <div dangerouslySetInnerHTML={{ __html: props.initialContent }} />
                    )}
                </Collapse>
            ) : (
                <React.Fragment>
                    <QuestionBlockHeader label={label} question={question} nbQuestion={nbQuestion} />
                    {'edit' === props.mode ? (
                        <TextEditor
                            initialContent={initialContent}
                            maxLength={1700}
                            onChange={htmlContent => onTextChange(htmlContent)}
                            placeholder={placeholder}
                        />
                    ) : (
                        <div dangerouslySetInnerHTML={{ __html: props.initialContent }} />
                    )}
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
    mode: PropTypes.oneOf(['edit', 'contribute']),
    nbQuestion: PropTypes.number.isRequired,
    onTextChange: PropTypes.func.isRequired,
    placeholder: PropTypes.string,
    question: PropTypes.string.isRequired,
};

export default QuestionBlock;
