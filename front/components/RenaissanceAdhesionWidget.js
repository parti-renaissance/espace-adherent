import React from 'react';
import RenaissanceAdhesionAmountChooser from "./RenaissanceAdhesionAmountChooser";

export default class RenaissanceAdhesionWidget extends React.Component {
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
                <RenaissanceAdhesionAmountChooser name={'montant'} onChange={this.handleAmountChange} />
                <button
                    className={'btn-fat btn-fat--red adhesion-button'}
                    type="submit"
                    key={`amount-${this.state.amount}`}
                    onClick={this.handleSubmitClick}
                >J'ADHÈRE</button>
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
