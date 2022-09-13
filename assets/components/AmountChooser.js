import React from 'react';
import PropTypes from 'prop-types';

const defaultAmounts = [20, 50, 120, 500];

const amountAfterTaxReturn = (amount) => {
    amount = parseInt(amount);

    if (!amount || 0 >= amount) {
        return '0,00';
    }

    return (amount * 0.34).toFixed(2);
};

export default class AmountChooser extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            amount: props.value,
        };

        this.handleButtonClicked = this.handleButtonClicked.bind(this);
        this.handleInputChange = this.handleInputChange.bind(this);
        this.handleInputKeyPress = this.handleInputKeyPress.bind(this);
    }

    handleButtonClicked(amount) {
        if (this.props.onChange) {
            this.props.onChange(amount);
        }

        this.refs.other_amount.value = '';

        this.setState({
            amount,
        });
    }

    handleInputChange(event) {
        if (this.props.onChange) {
            this.props.onChange(event.target.value);
        }

        this.setState({
            amount: event.target.value,
        });
    }

    handleInputKeyPress(event) {
        if (!this.props.onSubmit) {
            return;
        }

        const key = event.keyCode || event.charCode;

        if (13 === key) {
            event.preventDefault();
            this.props.onSubmit();
        }
    }

    render() {
        const state = this.state.amount;
        const classSelected = 'renaissance-amount-chooser__button--selected';

        return (
            <div className="renaissance-amount-chooser">
                <input type="hidden" name={this.props.name} value={state} key={`selected_amount_${state}`}/>
                <div className="flex mt-5 mb-5" role="group">
                    {this.props.amounts.map((amount) => (
                        <button className={`renaissance-amount-chooser__button ${amount === state ? classSelected : ''}`}
                                type="button"
                                onClick={() => this.handleButtonClicked(amount)}
                                key={`amount_${amount}`}>
                            {amount} €
                        </button>
                    ))}
                </div>

                <div className="renaissance-amount-chooser__other">
                    <input
                        type="number"
                        className="border-white/20 focus:border-white focus:ring-0 border-2 rounded-lg p-5 mb-5 bg-green text-white font-medium text-sm placeholder:text-white placeholder:font-medium placeholder:text-sm"
                        id="renaissance-amount-chooser__other__input"
                        placeholder="Autre montant"
                        min="1.0"
                        max={this.props.maxValue}
                        step="0.5"
                        ref="other_amount"
                        onFocus={this.handleInputChange}
                        onChange={this.handleInputChange}
                        onKeyPress={this.handleInputKeyPress}
                        defaultValue={
                            0 >= this.props.value || -1 < this.props.amounts.indexOf(this.props.value)
                                ? null : this.props.value
                        }
                    />
                </div>

                {this.state.amount
                    ? <div className="renaissance-donation__amount-chooser__after-taxes text-white text-center mb-5">
                        <p className="font-normal text-3xl leading-10">
                            {amountAfterTaxReturn(this.state.amount)} €
                        </p>
                        <div className={'font-medium text-sm'}>
                            après réduction d’impôts
                            <div className="renaissance-infos-taxe-reduction">
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
                    </div>
                    </div> : ''
                }
            </div>
        );
    }
}

AmountChooser.defaultProps = {
    maxValue: 7500,
    amounts: defaultAmounts,
};

AmountChooser.propTypes = {
    name: PropTypes.string.isRequired,
    amounts: PropTypes.arrayOf(PropTypes.number),
    value: PropTypes.number,
    maxValue: PropTypes.number,
    onChange: PropTypes.func,
    onSubmit: PropTypes.func,
};
