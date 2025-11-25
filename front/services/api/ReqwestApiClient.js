import qs from 'qs';

export default class ReqwestApiClient {
    constructor(reqwest) {
        this._reqwest = reqwest;
    }

    getPostalCodeCities(postalCode, callback) {
        this._createRequest(callback, {
            url: `/api/postal-code/${postalCode}`,
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

    getCommitteeTimelineFeed(committeeSlug, offset, callback) {
        this._createRequest(callback, {
            url: `/comites/${committeeSlug}/timeline?offset=${offset}`,
            type: 'html',
        });
    }

    getSearchResults(query, radius, city, type, offset, eventCategory, referentEvents, callback) {
        let url = `/recherche?q=${query}&r=${radius}&c=${city}&t=${type}&offset=${offset}`;

        if (eventCategory) {
            url += `&ec=${eventCategory}`;
        }

        if (referentEvents) {
            url += `&re=${referentEvents}`;
        }

        const request = this._reqwest({
            url,
            type: 'html',
        });

        request.then((response) => {
            callback(response);
        });

        request.fail(() => {
            callback(null);
        });
    }

    getCandidates(callback) {
        this._createRequest(callback, {
            url: '/api/candidates',
            type: 'json',
        });
    }

    toggleCommitteeMembership(committeeSlug, action, token, callback) {
        const request = this._reqwest({
            url: `/comites/${committeeSlug}/${action}`,
            type: 'html',
            method: 'post',
            data: {
                token,
            },
        });

        request.then((response) => {
            callback(JSON.parse(response));
        });

        request.fail(() => {
            callback(null);
        });
    }

    unregisterEvent(eventSlug, token, callback) {
        const request = this._reqwest({
            url: `/evenements/${eventSlug}/desinscription`,
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

    _createRequest(callback, parameters) {
        const request = this._reqwest(parameters);

        request.then((response) => {
            callback(response);
        });

        request.fail(() => {
            callback(null);
        });
    }

    getAssessorRequests(queryString, page, callback) {
        this._createRequest(callback, {
            url: `/espace-responsable-assesseur/plus?page=${page}&${queryString}`,
            type: 'html',
        });
    }

    getVotePlaces(queryString, page, callback) {
        this._createRequest(callback, {
            url: `/espace-responsable-assesseur/vote-places/plus?page=${page}&${queryString}`,
            type: 'html',
        });
    }

    getCommitteeCandidacies(uuid, callback) {
        this._createRequest(callback, {
            url: `/api/committees/${uuid}/candidacies`,
            type: 'json',
        });
    }

    getCommitteeAvailableMemberships({ slug, query }, callback, errorCallback) {
        this._reqwest({
            url: `/api/committee/${slug}/candidacy/available-memberships?${query ? `query=${query}` : ''}`,
            type: 'json',
        }).then((response) => callback(response)).fail((response) => errorCallback(JSON.parse(response.response)));
    }
}
