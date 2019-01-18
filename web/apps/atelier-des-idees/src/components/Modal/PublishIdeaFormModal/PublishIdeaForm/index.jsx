import React from 'react';
import PropTypes from 'prop-types';
import FirstForm from '../FirstForm';
import SecondForm from '../SecondForm';
import SuccessForm from '../StateForm/SuccessForm';
import FailForm from '../StateForm/FailForm';

class PublishIdeaForm extends React.Component {
    constructor(props) {
        super(props);
        const { description, theme } = props.defaultValues;
        this.state = {
            firstForm: { description: description || '', theme: theme || [], locality: [] },
            secondForm: {
                author: [],
                difficulties: [],
                legal: false,
            },
            currentPage: 1,
        };

        this.formatFormData = this.formatFormData.bind(this);
        this.submitForm = this.submitForm.bind(this);
    }

    componentDidMount() {
        window.scrollTo(0, 0);
    }

    handleFirstForm(res) {
        this.setState({ firstForm: res, currentPage: 2 });
    }

    handleSecondForm(res) {
        this.setState({ secondForm: res }, () => {
            this.submitForm();
        });
    }

    formatFormData() {
        const { firstForm, secondForm } = this.state;
        // format first form
        const { description, locality, theme } = firstForm;
        const themes = theme.map(themeItem => themeItem.value);
        const category = locality[0].value;
        const firstFormData = { description, themes, category };
        // format second form
        const { difficulties, committee } = secondForm;
        const needs = difficulties.map(diff => diff.value);
        const secondFormData = { needs };
        if (committee) {
            secondFormData.committee = committee[0].value;
        }
        return { ...firstFormData, ...secondFormData };
    }

    submitForm() {
        const formattedData = this.formatFormData();
        this.props.submitForm(formattedData);
    }

    saveForm(res) {
        this.setState({ secondForm: res });
    }

    goBack() {
        this.setState({ currentPage: 1 });
    }

    render() {
        return (
            <div className="publish-idea-form">
                {!this.props.isSubmitSuccess && !this.props.isSubmitError && (
                    <React.Fragment>
                        <div className="publish-idea-form__header">
                            {2 === this.state.currentPage && !this.props.isSubmitting && (
                                <button className="publish-idea-form__header__previous" onClick={() => this.goBack()}>
                                    ← Précédent
                                </button>
                            )}
                            <p className="publish-idea-form__header__paging">
                                <span className="publish-idea-form__header__paging--current">
                                    {this.state.currentPage}{' '}
                                </span>
                                / 2
                            </p>
                        </div>

                        {1 === this.state.currentPage && (
                            <FirstForm
                                defaultValues={
                                    0 === Object.keys(this.state.firstForm).length ? undefined : this.state.firstForm
                                }
                                themeOptions={this.props.themeOptions}
                                localityOptions={this.props.localityOptions}
                                onSubmit={res => this.handleFirstForm(res)}
                            />
                        )}
                        {2 === this.state.currentPage && (
                            <SecondForm
                                defaultValues={
                                    0 === Object.keys(this.state.secondForm).length ? undefined : this.state.secondForm
                                }
                                authorOptions={this.props.authorOptions}
                                committeeOptions={this.props.committeeOptions}
                                difficultiesOptions={this.props.difficultiesOptions}
                                isSubmitting={this.props.isSubmitting}
                                onSubmit={res => this.handleSecondForm(res)}
                                saveStateFormOnChange={res => this.saveForm(res)}
                            />
                        )}
                    </React.Fragment>
                )}
                {this.props.isSubmitSuccess && <SuccessForm id={this.props.id} />}
                {this.props.isSubmitError && <FailForm submitAgain={() => this.submitForm()} />}
            </div>
        );
    }
}

PublishIdeaForm.defaultProps = {
    isSubmitting: false,
    isSubmitSuccess: false,
    isSubmitError: false,
};

PublishIdeaForm.propTypes = {
    themeOptions: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.string.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    localityOptions: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.string.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    authorOptions: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.string.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    committeeOptions: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.string.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    difficultiesOptions: PropTypes.arrayOf(
        PropTypes.shape({
            value: PropTypes.string.isRequired,
            label: PropTypes.string.isRequired,
        })
    ).isRequired,
    id: PropTypes.string,
    defaultValues: PropTypes.shape({
        description: PropTypes.string,
        theme: PropTypes.array,
        locality: PropTypes.array,
        author: PropTypes.array,
        difficulties: PropTypes.array,
        legal: PropTypes.boolean,
    }),
    submitForm: PropTypes.func.isRequired,
    isSubmitting: PropTypes.bool,
    isSubmitSuccess: PropTypes.bool,
    isSubmitError: PropTypes.bool,
};

export default PublishIdeaForm;
