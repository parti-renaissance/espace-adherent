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

    getCommitteeTimelineFeed(committeeUuid, committeeSlug, offset, callback) {
        let request = this._reqwest({
            url: '/comites/'+committeeUuid+'/'+committeeSlug+'/timeline?offset='+offset,
            type: 'html'
        });

        request.then((response) => {
            callback(response);
        });

        request.fail(() => {
            callback(null);
        });
    }
    
}
