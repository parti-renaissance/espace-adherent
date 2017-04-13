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

    getVoteOffices(country, callback) {
        this._createRequest(callback, {
            url: '/api/vote-offices/'+country,
            type: 'json'
        });
    }

    getCommitteeTimelineFeed(committeeUuid, committeeSlug, offset, callback) {
        this._createRequest(callback, {
            url: '/comites/'+committeeUuid+'/'+committeeSlug+'/timeline?offset='+offset,
            type: 'html'
        });
    }

    getSearchResults(query, radius, city, type, offset, callback) {
        let request = this._reqwest({
            url: '/recherche?q='+query+'&r='+radius+'&c='+city+'&t='+type+'&offset='+offset,
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

    getProcurationRequests(filtersQueryString, page, callback) {
        this._createRequest(callback, {
            url: '/espace-responsable-procuration/requests-list/'+page+'?'+filtersQueryString,
            type: 'html'
        });
    }

    getProcurationProposals(filtersQueryString, page, callback) {
        this._createRequest(callback, {
            url: '/espace-responsable-procuration/proposals-list/'+page+'?'+filtersQueryString,
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

    toggleCommitteeMembership(committeeUuid, committeeSlug, action, token, callback) {
        let request = this._reqwest({
            url: '/comites/'+committeeUuid+'/'+committeeSlug+'/'+action,
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

    _createRequest(callback, parameters) {
        let request = this._reqwest(parameters);

        request.then((response) => {
            callback(response);
        });

        request.fail(() => {
            callback(null);
        });
    }
}
