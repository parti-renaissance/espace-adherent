import React, { PropTypes } from 'react';
import AmountChooser from './AmountChooser';

const maxAbonnementAmount = 625;
const maxAmount = 7500;

export default class DonationAmountChooser extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            amount: props.value,
            abonnement: props.abonnement,
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
                <div className="amount-chooser__monthly form__radio">
                    <div>
                        <input
                            type="radio"
                            name="abonnement"
                            id="donation-one-time"
                            value="0"
                            defaultChecked={!this.state.abonnement}
                            onChange={() => this.setState({ abonnement: false })}
                        />
                        <label htmlFor="donation-one-time" id="donation-one-time_label">
                            Je donne une fois
                        </label>
                    </div>
                    <div>
                        <input
                            type="radio"
                            name="abonnement"
                            id="donation-monthly"
                            value="1"
                            defaultChecked={this.state.abonnement}
                            onChange={() => this.setState({ abonnement: true })}
                        />
                        <label htmlFor="donation-monthly" id="donation-monthly_label">
                            Je donne chaque mois (paiement automatique)
                        </label>
                    </div>
                </div>

                <AmountChooser
                    name={this.props.name}
                    value={this.props.value}
                    onChange={this.handleAmountChange}
                    maxValue={this.state.abonnement ? maxAbonnementAmount : maxAmount}
                />

                { this.state.abonnement && this.state.amount > maxAbonnementAmount &&
                    <div className="amount-chooser__help">
                        <p>
                            Votre don mensuel ne peut dépasser {maxAbonnementAmount} euros.
                            Si vous souhaitez donner plus, vous pourrez compléter avec un don ponctuel.
                        </p>
                    </div>
                }

                <div className="donation__amount-chooser__after-taxes">
                    <h3>{App.get('donation.tax_return_provider').getAmountAfterTaxReturn(this.state.amount)}€</h3>
                    <p>après réduction d'impôt</p>
                </div>
            </div>
        );
    }
}

DonationAmountChooser.propTypes = {
    name: PropTypes.string.isRequired,
    value: PropTypes.number,
    abonnement: PropTypes.bool,
};
