import reqwest from 'reqwest';
import RequestApiClient from './services/api/RequestApiClient';

/**
 * @param {Container} di
 */
export default (di) => {
    di.set('reqwest', () => reqwest);

    di.set('api', () => new RequestApiClient(di.get('reqwest')));
};
