import React, { PropTypes } from 'react';

const defaultAmounts = [10, 20, 30, 100, 150];

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
        this.setState({
            amount: amount,
        });
    }

    handleInputChange(event) {
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
                        placeholder="Autre montant"
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
    value: PropTypes.number
};
