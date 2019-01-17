import React from 'react';
import PropTypes from 'prop-types';
import QuestionBlock from '../QuestionBlock';

class CreateIdeaTool extends React.Component {
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

    componentWillUnmount() {
        if (this.props.isDraft && this.state.intervalId) {
            clearInterval(this.state.intervalId);
        }
    }

    render() {
        return (
            <article className="create-idea-tool">
                {this.props.guidelines.map((guideline, idx) => {
                    const nbQOffset = this.props.guidelines[idx - 1]
                        ? this.props.guidelines[idx - 1].questions.length
                        : 0;
                    return (
                        <section key={`guideline_${idx}`} className="create-idea-tool__start-section">
                            <div className="create-idea-tool__section-title">
                                {/* TODO: adapt to actual attribute name (category_name) */}
                                <p className="create-idea-tool__section-subtitle">{guideline.category_name}</p>
                                <h2 className="create-idea-tool__section-title__main">{guideline.name}</h2>
                            </div>
                            {guideline.questions.map(({ id, name, category, required, placeholder }, index) => (
                                <QuestionBlock
                                    isAuthor={this.props.isAuthor}
                                    isRequired={required}
                                    initialContent={this.props.values[id]}
                                    key={id}
                                    mode={this.props.isDraft ? 'edit' : 'contribute'}
                                    label={category}
                                    question={name}
                                    questionId={id}
                                    placeholder={placeholder}
                                    nbQuestion={nbQOffset + index + 1}
                                    onTextChange={(htmlContent, save = false) => {
                                        this.props.onQuestionTextChange(id, htmlContent, save);
                                    }}
                                    hasError={this.props.errors.includes(id.toString())}
                                />
                            ))}
                        </section>
                    );
                })}
            </article>
        );
    }
}

CreateIdeaTool.defaultProps = {
    errors: [],
    isAuthor: false,
    values: {},
    isDraft: false,
    onAutoSave: undefined,
};

CreateIdeaTool.propTypes = {
    errors: PropTypes.arrayOf(PropTypes.string),
    isAuthor: PropTypes.bool,
    isDraft: PropTypes.bool,
    onQuestionTextChange: PropTypes.func.isRequired,
    onAutoSave: PropTypes.func,
    values: PropTypes.object,
    guidelines: PropTypes.arrayOf(
        PropTypes.shape({
            questions: PropTypes.arrayOf({
                id: PropTypes.string,
                name: PropTypes.string,
                placeholder: PropTypes.string,
                position: PropTypes.number,
                required: PropTypes.bool,
            }),
            name: PropTypes.string,
            position: PropTypes.number,
        })
    ).isRequired,
};

export default CreateIdeaTool;
