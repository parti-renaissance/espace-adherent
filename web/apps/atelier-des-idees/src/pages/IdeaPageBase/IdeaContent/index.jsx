import React from 'react';
import PropTypes from 'prop-types';
import QuestionBlock from '../QuestionBlock';

class IdeaContent extends React.Component {
    constructor(props) {
        super(props);
        this.state = { intervalId: null };
    }

    componentDidMount() {
        if (this.props.isDraft && this.props.isAuthor && this.props.onAutoSave) {
            // save draft every minute
            const intervalId = setInterval(() => {
                this.props.onAutoSave();
            }, 60000);
            this.setState({ intervalId });
        }
    }

    componentDidUpdate(prevProps) {
        // stop autosave when idea is not a draft anymore
        if (this.props.isDraft !== prevProps.isDraft && false === this.props.isDraft) {
            clearInterval(this.state.intervalId);
        }
    }

    componentWillUnmount() {
        if (this.props.isDraft && this.state.intervalId) {
            clearInterval(this.state.intervalId);
        }
    }

    render() {
        return (
            <article className="idea-content">
                {this.props.guidelines.map((guideline, idx) => {
                    // choose between edit and contribute mode
                    const editMode = this.props.isDraft ? 'draft' : 'contribute';
                    return (
                        <section key={`guideline_${idx}`} className="idea-content__start-section">
                            {!this.props.isReading && (
                                <div className="idea-content__section-title">
                                    <h2 className="idea-content__section-title__main">{guideline.name}</h2>
                                </div>
                            )}
                            {guideline.questions.map(
                                ({ id, name, category, required, placeholder, position }, index) => {
                                    const content = this.props.values[id];
                                    // only show question with answer in reading mode
                                    if (this.props.isReading && !content) {
                                        return null;
                                    }
                                    return (
                                        <QuestionBlock
                                            isAuthor={this.props.isAuthor}
                                            isRequired={required}
                                            initialContent={content}
                                            key={id}
                                            mode={this.props.isReading ? 'read' : editMode}
                                            label={category}
                                            question={name}
                                            questionId={id}
                                            placeholder={placeholder}
                                            nbQuestion={position}
                                            onTextChange={(htmlContent, save = false) => {
                                                this.props.onQuestionTextChange(id, htmlContent, save);
                                            }}
                                            hasError={this.props.errors.includes(id.toString())}
                                        />
                                    );
                                }
                            )}
                        </section>
                    );
                })}
            </article>
        );
    }
}

IdeaContent.defaultProps = {
    errors: [],
    isAuthor: false,
    values: {},
    isDraft: false,
    isReading: false,
    onAutoSave: undefined,
};

IdeaContent.propTypes = {
    errors: PropTypes.arrayOf(PropTypes.string),
    isAuthor: PropTypes.bool,
    isDraft: PropTypes.bool,
    isReading: PropTypes.bool,
    onQuestionTextChange: PropTypes.func.isRequired,
    onAutoSave: PropTypes.func,
    values: PropTypes.object,
    guidelines: PropTypes.arrayOf(
        PropTypes.shape({
            questions: PropTypes.arrayOf(
                PropTypes.shape({
                    id: PropTypes.number,
                    name: PropTypes.string,
                    placeholder: PropTypes.string,
                    position: PropTypes.number,
                    required: PropTypes.bool,
                })
            ),
            name: PropTypes.string,
            position: PropTypes.number,
        })
    ).isRequired,
};

export default IdeaContent;
