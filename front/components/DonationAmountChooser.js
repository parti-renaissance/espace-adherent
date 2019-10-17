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
        this.handleAbonnementChange = this.handleAbonnementChange.bind(this);
    }

    handleAmountChange(amount) {
        this.setState({ amount });
    }

    handleAbonnementChange(abonnement) {
        this.setState({
            amount: null,
            abonnement,
        });
    }

    render() {
        return (
            <div className="donation__amount-chooser">
                <div className="amount-chooser__monthly em-form__row">
                    <div className="em-form__group em-form__radio--inline">
                        <div className="flex-top">
                            <div className="form__radio">
                                <input
                                    type="radio"
                                    name="abonnement"
                                    id="donation-one-time"
                                    value="0"
                                    defaultChecked={!this.state.abonnement}
                                    onChange={() => this.handleAbonnementChange(false)}
                                />
                                <label htmlFor="donation-one-time" id="donation-one-time_label">
                                    Une fois
                                </label>
                            </div>
                            <div className="form__radio">
                                <input
                                    type="radio"
                                    name="abonnement"
                                    id="donation-monthly"
                                    value="1"
                                    defaultChecked={this.state.abonnement}
                                    onChange={() => this.handleAbonnementChange(true)}
                                />
                                <label htmlFor="donation-monthly" id="donation-monthly_label">
                                    Tous les mois
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <AmountChooser
                    amounts={this.state.abonnement ? [5, 10, 25, 50] : [20, 50, 120, 500]}
                    name={this.props.name}
                    value={this.props.value}
                    onChange={this.handleAmountChange}
                    maxValue={this.state.abonnement ? maxAbonnementAmount : maxAmount}
                />

                { this.state.abonnement && this.state.amount > maxAbonnementAmount &&
                    <div className="amount-chooser__help">
                        Votre don mensuel ne peut dépasser {maxAbonnementAmount} euros.
                        Si vous souhaitez donner plus, vous pourrez compléter avec un don ponctuel.
                    </div>
                }

                {this.state.amount ?
                    <div className="donation__amount-chooser__after-taxes">
                        soit <span className="after-taxes-amount">
                            {App.get('donation.tax_return_provider').getAmountAfterTaxReturn(this.state.amount)} €
                        </span> après réduction d’impôt <div className="infos-taxe-reduction">
                        ?
                            <div className="infos-taxe-reduction__content">
                                <div>La réduction fiscale</div>
                                <p>
                                    66 % de votre don vient en déduction de votre impôt sur
                                    le revenu (dans la limite de 20 % du revenu imposable).
                                    <br /><br />
                                    <strong>Par exemple :</strong> un don de 100 € vous revient
                                    en réalité à 34 € et vous fait bénéficier
                                    d’une réduction d’impôt de 66 €. Le montant annuel de votre
                                    don ne peut pas excéder 7500 € par personne physique.
                                    <br /><br />
                                    Le reçu fiscal pour votre don de l’année N vous sera envoyé
                                    au 2e trimestre de l’année N+1.
                                </p>
                            </div>
                        </div>
                    </div> : ''
                }
            </div>
        );
    }
}

DonationAmountChooser.propTypes = {
    name: PropTypes.string.isRequired,
    value: PropTypes.number,
    abonnement: PropTypes.bool,
};
