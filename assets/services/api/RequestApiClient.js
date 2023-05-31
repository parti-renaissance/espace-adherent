import qs from 'qs';

export default class RequestApiClient {
    constructor(request) {
        this._request = request;
    }

    getMe(callback) {
        this._createRequest(callback, {
            url: '/api/users/me',
            type: 'json',
        });
    }

    getResubscribeEmailPayload(callback) {
        this._createRequest(callback, {
            url: '/api/resubscribe-email',
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

    unregisterEvent(slug, token, callback) {
        const request = this._request({
            url: `/espace-adherent/evenements/${slug}/desinscription`,
            type: 'html',
            method: 'post',
            data: {
                token,
            },
        });

        request.then((response) => {
            callback(JSON.parse(response));
        });

        request.fail((response) => {
            callback(JSON.parse(response));
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
