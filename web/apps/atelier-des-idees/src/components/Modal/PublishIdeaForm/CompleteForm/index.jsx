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
        console.log(res);
        this.setState(
            { firstForm: res },
            () => !Object.is(this.state.firstForm, {}) && this.setState({ pagination: 2 })
        );
    }

    handleSecondForm(res) {
        this.setState({ secondForm: res });
    }

    goToPrevious() {
        this.setState({ pagination: 1 });
    }

    render() {
        return (
            <div className="complete-form">
                <div className="complete-form__header">
                    {2 === this.state.pagination && (
                        <button className="complete-form__header__previous" onClick={() => this.goToPrevious()}>
							← Précédent
                        </button>
                    )}
                    <p className="complete-form__header__paging">{this.state.pagination}/2</p>
                </div>
                {1 === this.state.pagination && (
                    <FirstForm
                        initInputs={!Object.is(this.state.firstForm, {}) && this.state.firstForm}
                        themeOptions={this.props.themeOptions}
                        localityOptions={this.props.localityOptions}
                        onSubmit={res => this.handleFirstForm(res)}
                    />
                )}
                {2 === this.state.pagination && (
                    <SecondForm
                        initInputs={!Object.is(this.state.secondForm, {}) && this.state.secondForm}
                        authorOptions={this.props.authorOptions}
                        committeeOptions={this.props.committeeOptions}
                        difficultiesOptions={this.props.difficultiesOptions}
                        onSubmit={res => this.handleSecondForm(res)}
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
};

export default CompleteForm;
