import React from 'react';
import PropTypes from 'prop-types';
import AmountChooser from './AmountChooser';

const maxAmount = 7500;

export default class RenaissanceAdhesionAmountChooser extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            amount: props.value,
        };

        this.handleAmountChange = this.handleAmountChange.bind(this);
    }

    handleAmountChange(amount) {
        if (this.props.onChange) {
            this.props.onChange(amount);
        }

        this.setState({ amount });
    }

    render() {
        return (
            <div className="adhesion__amount-chooser">
                <AmountChooser
                    amounts={[1, 10, 20, 30]}
                    name={this.props.name}
                    value={this.props.value}
                    onChange={this.handleAmountChange}
                    maxValue={maxAmount}
                />

                {this.state.amount
                    ? <div className="adhesion__amount-chooser__after-taxes">
                        soit <span className="after-taxes-amount">
                            {App.get('tax_return_provider').getAmountAfterTaxReturn(this.state.amount)} €
                        </span> après réduction d’impôt <div className="infos-taxe-reduction">
                        ?
                            <div className="infos-taxe-reduction__content">
                                <div>La réduction fiscale</div>
                                <p>
                                    66 % de votre cotisation vient en déduction de votre impôt sur
                                    le revenu (dans la limite de 20 % du revenu imposable).
                                    <br /><br />
                                    <strong>Par exemple :</strong> une cotisation de 100 € vous revient
                                    en réalité à 34 € et vous fait bénéficier
                                    d’une réduction d’impôt de 66 €. Le montant annuel de votre
                                    cotisation ne peut pas excéder 7500 €.
                                    <br /><br />
                                    Le reçu fiscal pour votre cotisation de l’année N vous sera envoyé
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

RenaissanceAdhesionAmountChooser.propTypes = {
    name: PropTypes.string.isRequired,
    value: PropTypes.number,
    onChange: PropTypes.func,
};
