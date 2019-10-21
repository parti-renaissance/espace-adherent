export default class TaxReturnProvider {
    getAmountAfterTaxReturn(amount) {
        amount = parseInt(amount);

        if (!amount || amount <= 0) {
            return '0,00';
        }

        return (amount * 0.34).toFixed(2);
    }
}
