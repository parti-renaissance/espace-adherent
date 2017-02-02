import React, { PropTypes } from 'react';
import AmountChooser from './AmountChooser';

export default class DonationAmountChooser extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            amount: props.value,
        };

        this.handleAmountChange = this.handleAmountChange.bind(this);
    }

    handleAmountChange(amount) {
        this.setState({
            amount,
        });
    }

    render() {
        return (
            <div className="donation__amount-chooser">
                <AmountChooser
                    name={this.props.name}
                    value={this.props.value}
                    onChange={this.handleAmountChange}
                />

                <div className="donation__amount-chooser__after-taxes">
                    <h3>
                    {App.get('donation.tax_return_provider').getAmountAfterTaxReturn(this.state.amount)}€
                    </h3>
                    <p>après réduction d'impôt</p>
                </div>
            </div>
        );
    }
}

DonationAmountChooser.propTypes = {
    name: PropTypes.string.isRequired,
    value: PropTypes.number,
};
