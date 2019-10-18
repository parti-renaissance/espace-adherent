import React, { PropTypes } from 'react';
import DonationAmountChooser from './DonationAmountChooser';

export default class DonationWidget extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            amount: null,
        };

        this.handleAmountChange = this.handleAmountChange.bind(this);
        this.handleSubmitClick = this.handleSubmitClick.bind(this);
    }

    render() {
        return (
            <div>
                <DonationAmountChooser name={'montant'} abonnement={false} onChange={this.handleAmountChange} />
                <button
                    className={'btn-fat btn-fat--red donation-button'}
                    type="submit"
                    key={`amount-${this.state.amount}`}
                    onClick={this.handleSubmitClick}
                >Je donne maintenant</button>
            </div>
        );
    }

    handleAmountChange(amount) {
        this.setState({ amount });
    }

    handleSubmitClick(event) {
        if (0 >= this.state.amount) {
            event.preventDefault();

            event.currentTarget.dataset.error = 'Vous devez choisir un montant';
            event.currentTarget.disabled = true;
        }
    }
}

DonationWidget.propTypes = {

};
