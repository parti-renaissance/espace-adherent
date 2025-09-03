import qs from 'qs';

export default class RequestApiClient {
    constructor(request) {
        this._request = request;
    }

    getResubscribeEmailPayload(callback) {
        this._createRequest(callback, {
            url: '/api/resubscribe-email',
            type: 'json',
        });
    }

    saveResubscribeStatus(uuid, response, apiKey) {
        this._createRequest(() => {}, {
            url: `/mailchimp/update-cleaned/${uuid}/save-last-response`,
            method: 'put',
            data: JSON.stringify({data: response}),
            contentType: 'application/json',
            headers: { 'X-API-KEY': apiKey },
            type: 'json',
        });
    }

    sendResubscribeEmail(url, body, callback) {
        this._createRequest(callback, {
            url: `${url}/subscribe/post-json`,
            method: 'post',
            type: 'jsonp',
            jsonpCallback: 'c',
            data: qs.stringify(body),
        });
    }

    _createRequest(callback, params) {
        const request = this._request(params);

        request.then((response) => {
            callback(response);
        });

        request.fail(() => {
            callback(null);
        });
    }
}
