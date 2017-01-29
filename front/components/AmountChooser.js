import React, { PropTypes } from 'react';

const defaultAmounts = [500, 200, 100, 70, 50, 20, 10];

export default class AmountChooser extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            amount: props.value
        };

        this.handleButtonClicked = this.handleButtonClicked.bind(this);
        this.handleInputChange = this.handleInputChange.bind(this);
    }

    handleButtonClicked(amount) {
        this.props.onChange && this.props.onChange(amount);

        this.setState({
            amount: amount,
        });
    }

    handleInputChange(event) {
        this.props.onChange && this.props.onChange(event.target.value);

        this.setState({
            amount: event.target.value,
        });
    }

    render() {
        return (
            <div className="amount-chooser">
                <input type="hidden" name={this.props.name} value={this.state.amount} />

                {defaultAmounts.map((amount) => {
                    return (
                        <button className={"amount-chooser__button "+(amount === this.state.amount ? 'amount-chooser__button--selected' : '')}
                                type="button"
                                onClick={() => this.handleButtonClicked(amount)}
                                key={"amount_"+amount}>
                            {amount} €
                        </button>
                    );
                })}

                <div className="amount-chooser__other">
                    <input
                        type="text"
                        className="amount-chooser__other__input"
                        id="amount-chooser__other__input"
                        placeholder="Autre"
                        onChange={this.handleInputChange}
                        defaultValue={defaultAmounts.indexOf(this.props.value) > -1 ? null : this.props.value} />

                    <label htmlFor="amount-chooser__other__input"
                           className="amount-chooser__other__label">
                        <span>Entrez un autre montant</span>
                        €
                    </label>
                </div>
            </div>
        );
    }
}

AmountChooser.propTypes = {
    name: PropTypes.string.isRequired,
    value: PropTypes.number,
    onChange: PropTypes.func,
};
