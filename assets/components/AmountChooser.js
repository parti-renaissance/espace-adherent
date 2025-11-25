import React from 'react';
import PropTypes from 'prop-types';

const amountAfterTaxReturn = (amount) => {
    amount = parseInt(amount, 10);

    if (!amount || 0 >= amount) {
        return '0,00';
    }

    return (amount * 0.34).toFixed(2);
};

export default class AmountChooser extends React.Component {
    constructor(props) {
        super(props);

        this.inputRef = React.createRef();
        this.state = {
            amount: props.value,
            isValid: true,
            error: null,
        };

        this.handleButtonClicked = this.handleButtonClicked.bind(this);
        this.handleInputChange = this.handleInputChange.bind(this);
    }

    handleButtonClicked(amount) {
        if (this.props.onChange) {
            this.props.onChange(amount);
        }

        this.inputRef.current.value = '';

        this.setState({
            amount,
            isValid: true,
            error: null,
        });
    }

    handleInputChange(event) {
        if (this.props.onChange) {
            this.props.onChange(event.target.value);
        }

        const amount = event.target.value;
        const isValid = this.props.minValue <= amount && amount <= this.props.maxValue;

        this.setState({
            amount,
            isValid,
            error: isValid ? null : `Veuillez indiquer un montant entre ${this.props.minValue} et ${this.props.maxValue}`,
        });
    }

    render() {
        const state = this.state.amount;
        const classSelected = 'renaissance-amount-chooser__button--selected';

        return (
            <div className="renaissance-amount-chooser">
                <input type="hidden" name={this.props.name} value={state} key={`selected_amount_${state}`} />
                <div className="flex my-5" role="group">
                    {this.props.amounts.map((item) => (
                        <button
                            className={`renaissance-amount-chooser__button ${item.amount === state ? classSelected : ''}`}
                            type="button"
                            onClick={() => this.handleButtonClicked(item.amount)}
                            key={`amount_${item.amount}`}
                        >
                            {this.props.displayLabel && (
                                <span
                                    className="text-sm leading-5 text-gray-500"
                                    dangerouslySetInnerHTML={{
                                        __html: item.label,
                                    }}
                                />
                            )}
                            <span>{item.amount} €</span>
                        </button>
                    ))}
                </div>

                <div className={`renaissance-amount-chooser__other ${!this.state.isValid && 'renaissance-amount-chooser__other-error'}`}>
                    <input
                        type="number"
                        className="renaissance-amount-chooser__other__input"
                        placeholder="Autre montant"
                        min={this.props.minValue}
                        max={this.props.maxValue}
                        step="0.5"
                        ref={this.inputRef}
                        onChange={this.handleInputChange}
                        defaultValue={0 >= this.props.value || -1 < this.props.amounts.map((e) => e.amount).indexOf(this.props.value) ? null : this.props.value}
                    />

                    <span>{this.state.error}</span>
                </div>

                {this.state.amount ? (
                    <div className="renaissance-donation__amount-chooser__after-taxes text-center text-green mb-5">
                        <p className="font-normal text-3xl leading-10">{amountAfterTaxReturn(this.state.amount)} €</p>
                        <div className={'font-medium text-sm'}>
                            après réduction d’impôts
                            <div className="renaissance-infos-taxe-reduction">
                                ?
                                <div className="renaissance-infos-taxe-reduction__content">
                                    <div>La réduction fiscale</div>
                                    <p>
                                        66 % de votre don vient en réduction de votre impôt sur le revenu (dans la limite de 20 % du revenu imposable).
                                        <br />
                                        <br />
                                        <strong>Par exemple :</strong> un don de 100 € vous revient en réalité à 34 € et vous fait bénéficier d’une réduction d’impôt de 66 €. Le
                                        montant annuel de votre don ne peut pas excéder 7500 € par personne physique.
                                        <br />
                                        <br />
                                        Le reçu fiscal pour votre don de l’année N vous sera envoyé au 2e trimestre de l’année N+1.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                ) : (
                    ''
                )}
            </div>
        );
    }
}

AmountChooser.defaultProps = {
    minValue: 1.0,
    maxValue: 7500,
    displayLabel: false,
};

AmountChooser.propTypes = {
    name: PropTypes.string.isRequired,
    amounts: PropTypes.arrayOf(PropTypes.object).isRequired,
    value: PropTypes.number,
    minValue: PropTypes.number,
    maxValue: PropTypes.number,
    displayLabel: PropTypes.bool,
    onChange: PropTypes.func,
    onSubmit: PropTypes.func,
};
