import React, { PropTypes } from 'react';
import DonationAmountChooser from '../components/DonationAmountChooser';

export default class DonationIndexAmoutChooser extends React.Component
{
    constructor() {
        super();

        this.state = {
            amount: null
        };

        this.handleAmountChange = this.handleAmountChange.bind(this);
    }

    componentDidMount() {
        this.setState({ amount: this.props.defaultAmount });
    }

    handleAmountChange(amount) {
        this.setState({ amount: amount });
    }

    render() {
        let amountAfterTaxes = this.state.amount ? (this.state.amount * 0.33).toFixed(2) : '--,--';

        return (
            <div className="donation__form__amount">
                {this.state.amount !== null ?
                    <div>
                        <input type="hidden" name="app_donation[amount]" value={this.state.amount} />

                        <DonationAmountChooser
                            onAmountChange={this.handleAmountChange}
                            currentAmount={this.state.amount}
                        />
                    </div>
                : ''}

                <div className="donation__form__reductions text--center">
                    Soit {amountAfterTaxes} € après réduction d'impôts
                </div>
            </div>
        );
    }
};

DonationIndexAmoutChooser.propTypes = {
    defaultAmount: PropTypes.number.isRequired
};
