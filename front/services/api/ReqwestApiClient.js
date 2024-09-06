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

    getCountryConsulates(country, callback) {
        this._createRequest(callback, {
            url: `/api/vote-offices/${country}`,
            type: 'json',
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

    getReferents(callback) {
        this._createRequest(callback, {
            url: '/api/referents',
            type: 'json',
        });
    }

    getApproaches(callback) {
        this._createRequest(callback, {
            url: '/api/programmatic-foundation/approaches',
            type: 'json',
        });
    }

    getCommittees(callback) {
        this._createRequest(callback, {
            url: '/api/committees',
            type: 'json',
        });
    }

    getCandidates(callback) {
        this._createRequest(callback, {
            url: '/api/candidates',
            type: 'json',
        });
    }

    getUpcomingEvents(callback) {
        let url = '/api/upcoming-events';
        const type = dom('#map-config').getAttribute('data-event-type');

        if ('' !== type) {
            url = `${url}?type=${type}`;
        }

        this._createRequest(callback, {
            url,
            type: 'json',
        });
    }

    getFacebookPicture(url, callback) {
        this._createRequest(callback, {
            url,
            type: 'text',
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

    getMessageStatus(messageId, callback, errorCallback) {
        this._reqwest({
            url: `/api/adherent_messages/${messageId}`,
            type: 'json',
        }).then((response) => callback(response)).fail((response) => errorCallback(response));
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

    getMessageStatistics(uuid, callback) {
        this._createRequest(callback, {
            url: `/adherent-message/${uuid}/statistics`,
            type: 'json',
        });
    }

    createUserSegmentList(data, callback) {
        this._createRequest(callback, {
            method: 'post',
            contentType: 'application/json',
            url: '/api/adherent-segments',
            type: 'json',
            data: JSON.stringify(data),
            processData: false,
        });
    }

    getAdherentCommittees(uuid, callback) {
        this._createRequest(callback, {
            url: `/api/adherents/${uuid}/committees`,
            type: 'json',
        });
    }

    toggleCommitteeVoteStatus(slug, token, enabled, callback) {
        this._reqwest({
            method: 'post',
            type: 'json',
            url: `/comites/${slug}/${enabled ? '' : 'ne-plus-'}voter`,
            data: { token },
        }).then(callback).fail((response) => callback(JSON.parse(response.responseText)));
    }

    getCommitteeCandidacies(uuid, callback) {
        this._createRequest(callback, {
            url: `/api/committees/${uuid}/candidacies`,
            type: 'json',
        });
    }

    saveUserListDefinitionMembers(memberType, type, data, callback) {
        this._createRequest(callback, {
            method: 'post',
            type: 'json',
            url: `/api/${memberType}/user-list-definitions/members/save`,
            data,
        });
    }

    getUserListDefinitionsForType(memberType, type, data, callback) {
        this._createRequest(callback, {
            method: 'post',
            type: 'json',
            url: `/api/${memberType}/user-list-definitions/${type}/members`,
            data,
        });
    }

    getCommitteeAvailableMemberships({ slug, query }, callback, errorCallback) {
        this._reqwest({
            url: `/api/committee/${slug}/candidacy/available-memberships?${query ? `query=${query}` : ''}`,
            type: 'json',
        }).then((response) => callback(response)).fail((response) => errorCallback(JSON.parse(response.response)));
    }
}
