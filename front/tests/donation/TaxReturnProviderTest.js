import { assert } from 'chai';
import TaxReturnProvider from '../../services/donation/TaxReturnProvider';

describe('TaxReturnProvider', () => {
    let provider = new TaxReturnProvider();

    it('returns valid output for valid amount', () => {
        assert.equal('3.40', provider.getAmountAfterTaxReturn(10));
        assert.equal('17.00', provider.getAmountAfterTaxReturn(50));
    });

    it('returns zero output for invalid amount', () => {
        assert.equal('0.00', provider.getAmountAfterTaxReturn('test'));
        assert.equal('0.00', provider.getAmountAfterTaxReturn(provider));
        assert.equal('0.00', provider.getAmountAfterTaxReturn('-10'));
    });
});
