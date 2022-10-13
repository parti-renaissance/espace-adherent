import React from 'react';
import PropTypes from 'prop-types';

const amountAfterTaxReturn = (amount) => {
    amount = parseInt(amount, 10);

    if (!amount || 0 >= amount) {
        return '0,00';
    }

    return (amount * 0.34).toFixed(2);
};

export default class AdhesionTaxReturn extends React.Component {
    render() {
        const { value } = this.props;
        return (
            <div className="renaissance-donation__amount-chooser__after-taxes text-black text-center mb-5">
                <p className="font-normal text-3xl leading-10">
                    {amountAfterTaxReturn(value)} €
                </p>
                <div className={'font-medium text-sm'}>
                    après réduction d’impôts
                    <div className="renaissance-infos-taxe-reduction">
                        ?
                        <div className="renaissance-infos-taxe-reduction__content">
                            <div>La réduction fiscale</div>
                            <p>
                                66 % de votre cotisation vient en déduction de votre impôt sur
                                le revenu (dans la limite de 20 % du revenu imposable).
                                <br /><br />
                                <strong>Par exemple :</strong> un cotisation de 30 € vous revient
                                en réalité à 10.20 € et vous fait bénéficier
                                d’une réduction d’impôt de 19.80 €. Il est en cumul avec le montant annuel de vos
                                don qui ne peut pas excéder 7500 € par personne physique.
                                <br /><br />
                                Le reçu fiscal pour votre cotisation et votre don de l’année N vous sera envoyé
                                au 2e trimestre de l’année N+1.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

AdhesionTaxReturn.propTypes = {
    value: PropTypes.number,
};
