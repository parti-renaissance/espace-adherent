import React from 'react';
import PropTypes from 'prop-types';
import QuestionBlock from '../QuestionBlock';

function CreateIdeaTool(props) {
    return (
        <article className="create-idea-tool">
            {props.guidelines.map((guideline, idx) => (
                <section key={`guideline_${idx}`} className="create-idea-tool__start-section">
                    <div className="create-idea-tool__section-title">
                        {/* TODO: uncomment */}
                        {/* <p className="create-idea-tool__section-subtitle">{guideline.category_name}</p>*/}
                        <h2 className="create-idea-tool__section-title__main">{guideline.name}</h2>
                    </div>
                    {guideline.questions.map(({ id, name, required, placeholder }, index) => (
                        <QuestionBlock
                            isAuthor={props.isAuthor}
                            canCollapse={!required}
                            initialContent={props.values[id]}
                            key={id}
                            mode={props.isEditing ? 'edit' : 'contribute'}
                            question={name}
                            placeholder={placeholder}
                            nbQuestion={index + 1}
                            onTextChange={(htmlContent, save = false) =>
                                props.onQuestionTextChange(id, htmlContent, save)
                            }
                        />
                    ))}
                </section>
            ))}
        </article>
    );
}

CreateIdeaTool.defaultProps = {
    isAuthor: false,
    values: {},
    isEditing: false,
};

CreateIdeaTool.propTypes = {
    isAuthor: PropTypes.bool,
    isEditing: PropTypes.bool,
    onQuestionTextChange: PropTypes.func.isRequired,
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
