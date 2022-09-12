import React from 'react';
import PropTypes from 'prop-types';
import NewAmountChooser from '../NewAmountChooser';

const maxAbonnementAmount = 625;
const maxAmount = 7500;

export default class RenaissanceDonationAmountChooser extends React.Component {
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
            <div className={'pb-12'}>
                <div className="inline-flex justify-center space-x-5">
                    <div>
                        <input
                            type="radio"
                            className="form-radio"
                            name="abonnement"
                            id="donation-one-time"
                            value="0"
                            defaultChecked={!this.state.abonnement}
                            onChange={() => this.handleAbonnementChange(false)}
                        />
                        <label htmlFor="donation-one-time" id="donation-one-time_label">
                            Non! Une fois seulement
                        </label>
                    </div>
                    <div>
                        <input
                            type="radio"
                            className="form-radio"
                            name="abonnement"
                            id="donation-monthly"
                            value="1"
                            defaultChecked={this.state.abonnement}
                            onChange={() => this.handleAbonnementChange(true)}
                        />
                        <label htmlFor="donation-monthly" id="donation-monthly_label">
                            Oui, tous les mois
                        </label>
                    </div>
                </div>

                <NewAmountChooser
                    amounts={this.state.abonnement ? [5, 10, 25, 50] : [30, 60, 120, 500]}
                    key={`amount-abo-${this.state.abonnement}`}
                    name={this.props.name}
                    value={this.props.value}
                    onChange={this.handleAmountChange}
                    maxValue={this.state.abonnement ? maxAbonnementAmount : maxAmount}
                />

                { this.state.abonnement && this.state.amount > maxAbonnementAmount
                    && <div className="text-xs">
                        Votre don mensuel ne peut dépasser {maxAbonnementAmount} euros.
                        Si vous souhaitez donner plus, vous pourrez compléter avec un don ponctuel.
                    </div>
                }

                {this.state.amount
                    ? <div className="renaissance-donation__amount-chooser__after-taxes">
                        soit <span className="renaissance-after-taxes-amount">
                            {amountAfterTaxReturn(this.state.amount)} €
                        </span> après réduction d’impôt <div className="renaissance-infos-taxe-reduction">
                        ?
                        <div className="renaissance-infos-taxe-reduction__content">
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

RenaissanceDonationAmountChooser.propTypes = {
    name: PropTypes.string.isRequired,
    value: PropTypes.number,
    abonnement: PropTypes.bool,
    onChange: PropTypes.func,
};
