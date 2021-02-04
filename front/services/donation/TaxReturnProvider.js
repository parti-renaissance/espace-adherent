export default class TaxReturnProvider {
    getAmountAfterTaxReturn(amount) {
        amount = parseInt(amount);

        if (!amount || 0 >= amount) {
            return '0,00';
        }

        return (amount * 0.34).toFixed(2);
    }
}
