export default class ReqwestApiClient {
    constructor(reqwest) {
        this._reqwest = reqwest;
    }

    getPostalCodeCities(postalCode, callback) {
        this._createRequest(callback, {
            url: '/api/postal-code/'+postalCode,
            type: 'json'
        });
    }

    getCountryConsulates(country, callback) {
        this._createRequest(callback, {
            url: '/api/vote-offices/'+country,
            type: 'json'
        });
    }

    getCommitteeTimelineFeed(committeeSlug, offset, callback) {
        this._createRequest(callback, {
            url: '/comites/'+committeeSlug+'/timeline?offset='+offset,
            type: 'html'
        });
    }

    getSearchResults(query, radius, city, type, offset, eventCategory, callback) {
        var url = '/recherche?q='+query+'&r='+radius+'&c='+city+'&t='+type+'&offset='+offset;

        if (eventCategory) {
            url += '&ec='+eventCategory;
        }

        let request = this._reqwest({
            url: url,
            type: 'html'
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
            type: 'json'
        });
    }

    getCommittees(callback) {
        this._createRequest(callback, {
            url: '/api/committees',
            type: 'json'
        });
    }

    getCandidates(callback) {
        this._createRequest(callback, {
            url: '/api/candidates',
            type: 'json'
        });
    }

    getProcurationRequests(queryString, page, callback) {
        this._createRequest(callback, {
            url: '/espace-responsable-procuration/plus?page='+page+'&'+queryString,
            type: 'html'
        });
    }

    getProcurationProposals(queryString, page, callback) {
        this._createRequest(callback, {
            url: '/espace-responsable-procuration/mandataires/plus?page='+page+'&'+queryString,
            type: 'html'
        });
    }

    getUpcomingEvents(callback) {
        var url = '/api/events';
        var type = dom('#map-config').getAttribute('data-event-type');

        if ('' !== type) {
            url = url + '?type=' + type;
        }

        this._createRequest(callback, {
            url:  url,
            type: 'json'
        });
    }

    getFacebookPicture(url, callback) {
        this._createRequest(callback, {
            url:  url,
            type: 'text'
        });
    }

    toggleCommitteeMembership(committeeSlug, action, token, callback) {
        let request = this._reqwest({
            url: '/comites/'+committeeSlug+'/'+action,
            type: 'html',
            method: 'post',
            data: {
                'token': token
            }
        });

        request.then((response) => {
            callback(JSON.parse(response));
        });

        request.fail(() => {
            callback(null);
        });
    }

    toggleCitizenProjectMembership(committeeSlug, action, token, callback) {
        let request = this._reqwest({
            url: '/projets-citoyens/'+committeeSlug+'/'+action,
            type: 'html',
            method: 'post',
            data: {
                'token': token
            }
        });

        request.then((response) => {
            callback(JSON.parse(response));
        });

        request.fail(() => {
            callback(null);
        });
    }

    unregisterFromCitizenAction(citizenActionSlug, token, callback) {
        let request = this._reqwest({
            url: '/action-citoyenne/'+citizenActionSlug+'/desinscription',
            type: 'html',
            method: 'post',
            data: {
                'token': token
            }
        });

        request.then((response) => {
            callback(JSON.parse(response));
        });

        request.fail((response) => {
            callback(JSON.parse(response));
        });
    }

    unregisterEvent(eventSlug, token, callback) {
        let request = this._reqwest({
            url: '/evenements/'+eventSlug+'/desinscription',
            type: 'html',
            method: 'post',
            data: {
                'token': token
            }
        });

        request.then((response) => {
            callback(JSON.parse(response));
        });

        request.fail((response) => {
            callback(JSON.parse(response));
        });
    }

    _createRequest(callback, parameters) {
        let request = this._reqwest(parameters);

        request.then((response) => {
            callback(response);
        });

        request.fail(() => {
            callback(null);
        });
    }

    deleteBoardMemberOnList(boardMemberId) {
        return this._reqwest({
            url: '/espace-membres-conseil/list/boardmember/' + boardMemberId,
            type: 'json',
            method: 'delete',
        });
    }

    addBoardMemberToList(boardMemberId) {
        return this._reqwest({
            url: '/espace-membres-conseil/list/boardmember',
            type: 'json',
            method: 'post',
            data: {
                'boardMemberId': boardMemberId,
            }
        });
    }

    getMessageStatus(messageId, callback, errorCallback) {
        this._reqwest({
            url: '/api/adherent_messages/' + messageId,
            type: 'json',
        }).then(response => callback(response)).fail((response) => errorCallback(response));
    }
}
