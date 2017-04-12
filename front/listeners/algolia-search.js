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
    const footer = dom('footer');

    on(dom('#je-cherche'), 'click', () => {
        addClass(overlay, 'g-search');
        addClass(banner, 'hide-me');
        addClass(header, 'hide-me');
        addClass(content, 'hide-me');
        addClass(footer, 'hide-me');

        dom('#search-input').focus();
    });

    on(dom('#je-ferme-la-recherche'), 'click', () => {
        removeClass(overlay, 'g-search');
        removeClass(banner, 'hide-me');
        removeClass(header, 'hide-me');
        removeClass(content, 'hide-me');
        removeClass(footer, 'hide-me');
    });

    render(
        <AlgoliaSearch appId={appId} appKey={appKey} environment={di.get('environment')} />,
        dom('#search-engine')
    );
};
