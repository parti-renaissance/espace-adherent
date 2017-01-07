import React, { PropTypes } from 'react';

const defaultAmounts = [10, 20, 30, 100, 150];

const DonationAmountChooser = (props) => {
    let defaultAmountsView = [],
        amountInList = false;

    defaultAmounts.forEach((amount) => {
        amountInList = amountInList || amount === props.currentAmount;

        defaultAmountsView.push(
            <input type="radio"
                   name="donation_amount"
                   value={amount}
                   id={"donation_amount_"+amount}
                   key={"input_donation_amount_"+amount}
                   checked={amount === props.currentAmount}
                   onChange={() => { props.onAmountChange && props.onAmountChange(amount); }}
            />
        );

        defaultAmountsView.push(
            <label htmlFor={"donation_amount_"+amount}
                   role="button"
                   key={"label_donation_amount_"+amount}
                   className="amount">
                {amount} €
            </label>
        );
    });

    return (
        <div className="donate__amounts">
            {defaultAmountsView}

            <div className="other-amount">
                <input
                    type="text"
                    id="donation_other_amount"
                    placeholder="Autre montant"
                    defaultValue={amountInList ? null : props.currentAmount}
                    onChange={(event) => { props.onAmountChange && props.onAmountChange(parseInt(event.target.value)); }}
                    onKeyPress={(event) => { event.charCode === 13 && props.onOtherAmountSubmit && props.onOtherAmountSubmit(); }}
                />

                <label htmlFor="donation_other_amount"
                       className="label">
                    <span>Entrez un autre montant</span>
                    €
                </label>
            </div>
        </div>
    );
};

DonationAmountChooser.propTypes = {
    currentAmount: PropTypes.number,
    onAmountChange: PropTypes.func,
    onOtherAmountSubmit: PropTypes.func,
};

export default DonationAmountChooser;
