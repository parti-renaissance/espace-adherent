import React from 'react';
import PropTypes from 'prop-types';
import FirstForm from '../FirstForm';
import SecondForm from '../SecondForm';

class PublishIdeaForm extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            firstForm: {},
            secondForm: {},
            currentPage: 1,
        };
    }

    handleFirstForm(res) {
        this.setState({ firstForm: res, currentPage: 2 });
    }

    handleSecondForm(res) {
        this.setState({ secondForm: res }, () => {
            this.props.submitForm({ ...this.state.firstForm, ...this.state.secondForm });
        });
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
                <div className="publish-idea-form__header">
                    {2 === this.state.currentPage && (
                        <button className="publish-idea-form__header__previous" onClick={() => this.goBack()}>
							← Précédent
                        </button>
                    )}
                    <p className="publish-idea-form__header__paging">
                        <span className="publish-idea-form__header__paging--current">{this.state.currentPage} </span>/ 2
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
                        onSubmit={res => this.handleSecondForm(res)}
                        saveStateFormOnChange={res => this.saveForm(res)}
                    />
                )}
            </div>
        );
    }
}

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
    submitForm: PropTypes.func.isRequired,
};

export default PublishIdeaForm;
