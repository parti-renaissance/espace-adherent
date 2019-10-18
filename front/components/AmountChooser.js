import React, { PropTypes } from 'react';

const defaultAmounts = [20, 50, 120, 500];

export default class AmountChooser extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            amount: props.value,
        };

        this.handleButtonClicked = this.handleButtonClicked.bind(this);
        this.handleInputChange = this.handleInputChange.bind(this);
        this.handleInputKeyPress = this.handleInputKeyPress.bind(this);
    }

    handleButtonClicked(amount) {
        if (this.props.onChange) {
            this.props.onChange(amount);
        }

        this.refs.other_amount.value = '';

        this.setState({
            amount,
        });
    }

    handleInputChange(event) {
        if (this.props.onChange) {
            this.props.onChange(event.target.value);
        }

        this.setState({
            amount: event.target.value,
        });
    }

    handleInputKeyPress(event) {
        if (!this.props.onSubmit) {
            return;
        }

        const key = event.keyCode || event.charCode;

        if (13 === key) {
            event.preventDefault();
            this.props.onSubmit();
        }
    }

    render() {
        const state = this.state.amount;
        const classSelected = 'amount-chooser__button--selected';

        return (
            <div className="amount-chooser">
                <input type="hidden" name={this.props.name} value={state} key={`selected_amount_${state}`}/>

                {this.props.amounts.map(amount => (
                    <button className={`amount-chooser__button ${amount === state ? classSelected : ''}`}
                            type="button"
                            onClick={() => this.handleButtonClicked(amount)}
                            key={`amount_${amount}`}>
                        {amount} €
                    </button>
                ))}

                <div className="amount-chooser__other">
                    <input
                        type="number"
                        className="amount-chooser__other__input"
                        id="amount-chooser__other__input"
                        placeholder="Autre montant"
                        min="0.01"
                        max={this.props.maxValue}
                        step="0.01"
                        ref="other_amount"
                        onFocus={this.handleInputChange}
                        onChange={this.handleInputChange}
                        onKeyPress={this.handleInputKeyPress}
                        defaultValue={-1 < this.props.amounts.indexOf(this.props.value) ? null : this.props.value}
                    />

                    <label htmlFor="amount-chooser__other__input" className="amount-chooser__other__label">
                        <span>Entrez un autre montant</span>€
                    </label>
                </div>
            </div>
        );
    }
}

AmountChooser.defaultProps = {
    maxValue: 7500,
    amounts: defaultAmounts,
};

AmountChooser.propTypes = {
    name: PropTypes.string.isRequired,
    amounts: PropTypes.arrayOf(PropTypes.number),
    value: PropTypes.number,
    maxValue: PropTypes.number,
    onChange: PropTypes.func,
    onSubmit: PropTypes.func,
};
