import VoteLocationForm from './VoteLocationForm';

export default class VoteLocationFormFactory {
    constructor(api) {
        this._api = api;
    }

    createVoteLocationForm(country, postalCode, city, cityName, office) {
        return new VoteLocationForm(
            this._api,
            dom('#'+country),
            dom('#'+postalCode),
            dom('#'+city),
            dom('#'+cityName),
            dom('#'+office)
        );
    }
}
