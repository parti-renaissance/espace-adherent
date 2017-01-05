"use strict";

import React from 'react';

import DonationAmountChooser from '../components/DonationAmountChooser';

export default class HomeDonation extends React.Component
{
    constructor() {
        super();

        this.state = {
            amount: 10
        };

        this.handleAmountChange = this.handleAmountChange.bind(this);
    }

    handleAmountChange(amount) {
        this.setState({
            amount: amount
        });
    }

    render() {
        return (
            <div>
                <DonationAmountChooser
                    onAmountChange={this.handleAmountChange}
                    currentAmount={this.state.amount}
                />

                <a href={Routing.generate('donation_index', { montant: this.state.amount })} title="Je donne"
                   className="form btn btn--primary btn--medium-small text--center btn--full">
                    Je donne
                    <svg width="10px" height="16px" viewBox="1 5 10 16" version="1.1">
                        <title>Right arrow</title>
                        <polyline id="right-arrow" stroke="#FFFFFF" strokeWidth="1" fill="none" points="2 6 10.14 12.9223586 2 19.8436338" />
                    </svg>
                </a>
            </div>
        );
    }
};
