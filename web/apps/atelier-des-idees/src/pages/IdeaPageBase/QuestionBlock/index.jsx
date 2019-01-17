import React from 'react';
import PropTypes from 'prop-types';
import TextEditor from '../../../components/TextEditor';
import Collapse from '../../../components/Collapse';
import IdeaThread from '../../../containers/IdeaThread';

function QuestionBlockHeader({ label, question, nbQuestion, isRequired }) {
    return (
        <h3 className="question-block-header">
            <span className="question-block-header__label">{`${nbQuestion}. ${label ? `${label} : ` : ''}`}</span>
            <span className="question-block-header__question">{question}</span>
            {isRequired && <span className="question-block-header__mandatory">(Obligatoire)</span>}
        </h3>
    );
}

function QuestionBlockBody(props) {
    return (
        <React.Fragment>
            {props.isAuthor && ('edit' === props.mode || props.isEditing) ? (
                <React.Fragment>
                    <TextEditor
                        initialContent={props.initialContent}
                        maxLength={1700}
                        onChange={htmlContent => props.onTextChange(htmlContent)}
                        placeholder={props.placeholder}
                        error={props.hasError ? 'Merci de répondre à cette question avant de poursuivre' : ''}
                    />
                </React.Fragment>
            ) : (
                <React.Fragment>
                    <div dangerouslySetInnerHTML={{ __html: props.initialContent }} />
                </React.Fragment>
            )}
            {'edit' !== props.mode && props.isAuthor && (
                <div className="question-block__editing-footer">
                    {props.isEditing ? (
                        <React.Fragment>
                            {!!props.initialContent && (
                                <button className="question-block__editing-footer__btn" onClick={props.onCancelAnswer}>
                                    Annuler
                                </button>
                            )}
                            {props.canSaveAnswer && (
                                <button
                                    className="question-block__editing-footer__btn editing-footer__btn--main"
                                    onClick={props.onSaveAnswer}
                                >
                                    Enregistrer
                                </button>
                            )}
                        </React.Fragment>
                    ) : (
                        <button
                            className="question-block__editing-footer__btn editing-footer__btn--main"
                            onClick={props.onEditAnswer}
                        >
                            Editer
                        </button>
                    )}
                </div>
            )}
        </React.Fragment>
    );
}

class QuestionBlock extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            // handle question block edition, can only true in contributing mode
            isEditing: 'edit' === props.mode ? false : !props.initialContent,
            value: this.props.initialContent,
        };
        this.onTextChange = this.onTextChange.bind(this);
        this.onSaveAnswer = this.onSaveAnswer.bind(this);
        this.onCancelAnswer = this.onCancelAnswer.bind(this);
        this.renderBody = this.renderBody.bind(this);
    }

    onTextChange(content) {
        if (this.state.isEditing) {
            this.setState({ value: content });
        } else {
            this.props.onTextChange(content);
        }
    }

    onSaveAnswer() {
        this.props.onTextChange(this.state.value, true);
        this.setState({ isEditing: false });
    }

    onCancelAnswer() {
        this.setState({ isEditing: false, value: this.props.initialContent });
    }

    renderBody() {
        const { placeholder, initialContent, isAuthor, mode, isRequired } = this.props;
        return (
            <React.Fragment>
                <QuestionBlockBody
                    isAuthor={isAuthor}
                    initialContent={initialContent}
                    placeholder={placeholder}
                    onTextChange={this.onTextChange}
                    mode={mode}
                    hasError={this.props.hasError}
                    isEditing={this.state.isEditing}
                    onEditAnswer={() => this.setState({ isEditing: true })}
                    onSaveAnswer={this.onSaveAnswer}
                    onCancelAnswer={this.onCancelAnswer}
                    canSaveAnswer={isRequired ? !!this.state.value : true}
                />
                {'edit' !== this.props.mode && (
                    <div className="question-block__threads">
                        <IdeaThread questionId={this.props.questionId} />
                    </div>
                )}
            </React.Fragment>
        );
    }

    render() {
        const { label, question, nbQuestion, isRequired } = this.props;
        return (
            <div className="question-block">
                {this.props.isRequired ? (
                    <React.Fragment>
                        <QuestionBlockHeader
                            label={label}
                            question={question}
                            nbQuestion={nbQuestion}
                            isRequired={isRequired}
                        />
                        {this.renderBody()}
                    </React.Fragment>
                ) : (
                    <Collapse
                        title={
                            <QuestionBlockHeader
                                label={label}
                                question={question}
                                nbQuestion={nbQuestion}
                                isRequired={isRequired}
                            />
                        }
                    >
                        {this.renderBody()}
                    </Collapse>
                )}
            </div>
        );
    }
}

QuestionBlock.defaultProps = {
    hasError: false,
    isRequired: false,
    initialContent: '',
    isAuthor: false,
    placeholder: undefined,
    questionId: undefined,
};

QuestionBlock.propTypes = {
    hasError: PropTypes.bool,
    isAuthor: PropTypes.bool,
    isRequired: PropTypes.bool,
    initialContent: PropTypes.string,
    label: PropTypes.string.isRequired,
    mode: PropTypes.oneOf(['edit', 'contribute']),
    nbQuestion: PropTypes.number.isRequired,
    onTextChange: PropTypes.func.isRequired,
    placeholder: PropTypes.string,
    question: PropTypes.string.isRequired,
    questionId: PropTypes.string,
};

export default QuestionBlock;
