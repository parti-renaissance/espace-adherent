import React from 'react';
import PropTypes from 'prop-types';
import FirstForm from '../FirstForm';
import SecondForm from '../SecondForm';

class CompleteForm extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            firstForm: {},
            secondForm: {},
            pagination: 1,
        };
    }

    handleFirstForm(res) {
        this.setState({ firstForm: res }, () => this.setState({ pagination: 2 }));
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
        this.setState({ pagination: 1 });
    }

    render() {
        return (
            <div className="complete-form">
                <div className="complete-form__header">
                    {2 === this.state.pagination && (
                        <button className="complete-form__header__previous" onClick={() => this.goBack()}>
							← Précédent
                        </button>
                    )}
                    <p className="complete-form__header__paging">
                        <span className="complete-form__header__paging--current">{this.state.pagination} </span>/ 2
                    </p>
                </div>
                {1 === this.state.pagination && (
                    <FirstForm
                        initInputs={0 === Object.keys(this.state.firstForm).length ? undefined : this.state.firstForm}
                        themeOptions={this.props.themeOptions}
                        localityOptions={this.props.localityOptions}
                        onSubmit={res => this.handleFirstForm(res)}
                    />
                )}
                {2 === this.state.pagination && (
                    <SecondForm
                        initInputs={0 === Object.keys(this.state.secondForm).length ? undefined : this.state.secondForm}
                        authorOptions={this.props.authorOptions}
                        committeeOptions={this.props.committeeOptions}
                        difficultiesOptions={this.props.difficultiesOptions}
                        onSubmit={res => this.handleSecondForm(res)}
                        saveForm={res => this.saveForm(res)}
                    />
                )}
            </div>
        );
    }
}

CompleteForm.propTypes = {
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

export default CompleteForm;
