/* eslint-disable no-console */

import React from 'react';
import { render } from 'react-dom';
import AlgoliaSearch from '../components/AlgoliaSearch';

/*
 * Algolia search
 */
export default (di) => {
    const appId = di.get('algoliaAppId');
    const appKey = di.get('algoliaAppPublicKey');

    if (!appId || !appKey) {
        console.log('Algolia is disabled because no credentials were provided');
        return;
    }

    const overlay = dom('#search-overlay');
    const banner = dom('#header-banner');
    const header = dom('header');
    const content = dom('main');
    const newsletter = dom('.newsletter__banner');
    const footer = dom('footer');

    const searchButtons = findAll(document, '.je-cherche');
    const searchCloseButton = dom('#je-ferme-la-recherche');
    const searchEngine = dom('#search-engine');

    if (!searchButtons || !searchCloseButton || !searchEngine) {
        return;
    }

    searchButtons.forEach((button) => {
        on(button, 'click', () => {
            addClass(overlay, 'g-search');

            [header, content, banner, newsletter, footer].forEach((item) => {
                if (item) {
                    addClass(item, 'hide-me');
                }
            });

            dom('#search-input').focus();
        });
    });

    on(searchCloseButton, 'click', () => {
        removeClass(overlay, 'g-search');

        [header, content, banner, newsletter, footer].forEach((item) => {
            if (item) {
                removeClass(item, 'hide-me');
            }
        });
    });

    render(<AlgoliaSearch appId={appId} appKey={appKey} environment={di.get('environment')} />, searchEngine);
};
