export default class ReqwestApiClient {
    constructor(reqwest) {
        this._reqwest = reqwest;
    }

    getPostalCodeCities(postalCode, callback) {
        let request = this._reqwest({
            url: '/api/postal-code/'+postalCode,
            type: 'json'
        });

        request.then((response) => {
            callback(response);
        });

        request.fail(() => {
            callback(null);
        });
    }
}
