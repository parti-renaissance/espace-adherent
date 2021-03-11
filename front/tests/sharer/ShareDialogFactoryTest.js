import { assert } from 'chai';
import ShareDialogFactory from '../../services/sharer/ShareDialogFactory';

describe('ShareDialogFactory', () => {
    const factory = new ShareDialogFactory();

    it('creates a Twitter dialog for a Twitter type', () => {
        const dialog = factory.createShareLink('twitter', 'http://example.org/sub', 'ExampleTitle');

        assert.include(dialog.getUrl(), 'twitter.com');
        assert.include(dialog.getUrl(), 'example.org/sub');
        assert.include(dialog.getUrl(), 'ExampleTitle');
    });

    it('creates a Facebook dialog for a Facebook type', () => {
        const dialog = factory.createShareLink('facebook', 'http://example.org/sub', 'ExampleTitle');

        assert.include(dialog.getUrl(), 'facebook.com');
        assert.include(dialog.getUrl(), 'example.org/sub');
    });
});
