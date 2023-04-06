import React from 'react';
import PropTypes from 'prop-types';
import AmountChooser from '../AmountChooser';

const maxAbonnementAmount = 625;
const maxAmount = 7500;

export default class DonationAmountChooser extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            amount: props.value,
            abonnement: props.abonnement,
            abonnementPrices: [
                { amount: 5 },
                { amount: 10 },
                { amount: 25 },
                { amount: 50 },
            ],
            noAbonnementPrices: [
                { amount: 30 },
                { amount: 60 },
                { amount: 120 },
                { amount: 500 },
            ],
        };

        this.handleAmountChange = this.handleAmountChange.bind(this);
        this.handleAbonnementChange = this.handleAbonnementChange.bind(this);
    }

    handleAmountChange(amount) {
        if (this.props.onChange) {
            this.props.onChange(amount);
        }

        this.setState({ amount });
    }

    handleAbonnementChange(abonnement) {
        if (this.props.onChange) {
            this.props.onChange(null);
        }

        this.setState({
            amount: null,
            abonnement,
        });
    }

    render() {
        return (
            <div>
                <div className="inline-flex justify-center space-x-5 text-green">
                    <div>
                        <input
                            type="radio"
                            className="form-radio"
                            name="duration"
                            id="donation-one-time"
                            value="0"
                            defaultChecked={!this.state.abonnement}
                            onChange={() => this.handleAbonnementChange(false)}
                        />
                        <label htmlFor="donation-one-time" id="donation-one-time_label" className={'ml-2'}>
                            Une fois seulement
                        </label>
                    </div>
                    <div>
                        <input
                            type="radio"
                            className="form-radio"
                            name="duration"
                            id="donation-monthly"
                            value="-1"
                            defaultChecked={this.state.abonnement}
                            onChange={() => this.handleAbonnementChange(true)}
                        />
                        <label htmlFor="donation-monthly" id="donation-monthly_label" className={'ml-2'}>
                            <span>tous les mois</span>
                        </label>
                    </div>
                </div>

                <AmountChooser
                    amounts={this.state.abonnement ? this.state.abonnementPrices : this.state.noAbonnementPrices}
                    key={`amount-abo-${this.state.abonnement}`}
                    name={this.props.name}
                    value={this.props.value}
                    onChange={this.handleAmountChange}
                    maxValue={this.state.abonnement ? maxAbonnementAmount : maxAmount}
                />

                { this.state.abonnement && this.state.amount > maxAbonnementAmount
                    && <div className="text-xs text-red-400">
                        Votre don mensuel ne peut dépasser {maxAbonnementAmount} euros.
                        Si vous souhaitez donner plus, vous pourrez compléter avec un don ponctuel.
                    </div>
                }
            </div>
        );
    }
}

DonationAmountChooser.propTypes = {
    name: PropTypes.string.isRequired,
    value: PropTypes.number,
    abonnement: PropTypes.bool,
    onChange: PropTypes.func,
};
