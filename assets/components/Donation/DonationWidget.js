import React from 'react';
import DonationAmountChooser from './DonationAmountChooser';
import DonationDestinationChooser from './DonationDestinationChooser';

export default class DonationWidget extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            amount: props.defaultAmount ?? null,
            destination: false,
        };

        this.handleAmountChange = this.handleAmountChange.bind(this);
        this.handleDestinationChange = this.handleDestinationChange.bind(this);
        this.handleSubmitClick = this.handleSubmitClick.bind(this);
    }

    render() {
        return (
            <div className={'renaissance-donation'}>
                <DonationAmountChooser name={'amount'} abonnement={'-1' === this.props.defaultDuration} value={this.state.amount} onChange={this.handleAmountChange} />
                <DonationDestinationChooser destination={false} onChange={this.handleDestinationChange} />
                <button
                    className="button button-green button-full donation-button mt-20"
                    type="submit"
                    key={`amount-${this.state.amount}`}
                    onClick={this.handleSubmitClick}
                >Je donne en ligne</button>
            </div>
        );
    }

    handleAmountChange(amount) {
        this.setState({ amount });
    }

    handleDestinationChange(destination) {
        this.setState({ destination });
    }

    handleSubmitClick(event) {
        if (0 >= this.state.amount) {
            event.preventDefault();

            event.currentTarget.dataset.error = 'Vous devez choisir un montant';
            event.currentTarget.disabled = true;
        }
    }
}
