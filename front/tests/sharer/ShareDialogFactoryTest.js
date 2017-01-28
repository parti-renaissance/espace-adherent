import { assert } from 'chai';
import ShareDialogFactory from '../../services/sharer/ShareDialogFactory';

describe('ShareDialogFactory', () => {
    let factory = new ShareDialogFactory();

    it('creates a Twitter dialog for a Twitter type', () => {
        let dialog = factory.createShareLink('twitter', 'http://example.org/sub', 'ExampleTitle');

        assert.include(dialog.getUrl(), 'twitter.com');
        assert.include(dialog.getUrl(), 'example.org/sub');
        assert.include(dialog.getUrl(), 'ExampleTitle');
    });

    it('creates a Facebook dialog for a Facebook type', () => {
        let dialog = factory.createShareLink('facebook', 'http://example.org/sub', 'ExampleTitle');

        assert.include(dialog.getUrl(), 'facebook.com');
        assert.include(dialog.getUrl(), 'example.org/sub');
    });
});
