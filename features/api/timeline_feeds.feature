@api
@renaissance_api
Feature:
    In order to get timeline feeds
    As a logged-in user
    I should be able to access timeline feeds API filtered by my roles

    Scenario: As a logged-in JeMengage Mobile user I can get timeline feeds
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/je-mengage/timeline_feeds"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "nbHits": 5,
                "page": 0,
                "hits": [
                    {
                        "type": "event",
                        "title": "Test event national",
                        "is_national": true,
                        "audience": {
                            "scope_targets": false
                        }
                    },
                    {
                        "type": "publication",
                        "title": "Test publication sans scope_target",
                        "audience": {
                            "scope_targets": false
                        }
                    },
                    {
                        "type": "publication",
                        "title": "Test publication avec scope_target deputy",
                        "audience": {
                            "scope_targets": true,
                            "include": ["scope_targets:deputy"]
                        }
                    },
                    {
                        "type": "publication",
                        "title": "Test publication avec scope_target team",
                        "audience": {
                            "scope_targets": true,
                            "include": ["scope_targets:president_departmental_assembly", "scope_targets:president_departmental_assembly:custom"]
                        }
                    }
                ]
            }
            """

    Scenario: As a logged-in user with a zone-based role (deputy) I can get timeline feeds
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/je-mengage/timeline_feeds"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "nbHits": 5,
                "page": 0,
                "hits": [
                    {
                        "type": "event",
                        "title": "Test event national"
                    },
                    {
                        "type": "publication",
                        "title": "Test publication sans scope_target"
                    },
                    {
                        "type": "publication",
                        "title": "Test publication avec scope_target deputy"
                    },
                    {
                        "type": "publication",
                        "title": "Test publication avec scope_target team"
                    }
                ]
            }
            """

    Scenario: As a logged-in user with multiple zone-based roles (president_departmental_assembly) I can get timeline feeds
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/je-mengage/timeline_feeds"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "nbHits": 5,
                "page": 0,
                "hits": [
                    {
                        "type": "event",
                        "title": "Test event national"
                    },
                    {
                        "type": "publication",
                        "title": "Test publication sans scope_target"
                    },
                    {
                        "type": "publication",
                        "title": "Test publication avec scope_target deputy"
                    },
                    {
                        "type": "publication",
                        "title": "Test publication avec scope_target team"
                    }
                ]
            }
            """

    Scenario: As a logged-in user with delegated access I can get timeline feeds
        Given I am logged with "senateur@en-marche-dev.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/je-mengage/timeline_feeds"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "nbHits": 5,
                "page": 0,
                "hits": [
                    {
                        "type": "event",
                        "title": "Test event national"
                    },
                    {
                        "type": "publication",
                        "title": "Test publication sans scope_target"
                    },
                    {
                        "type": "publication",
                        "title": "Test publication avec scope_target deputy"
                    },
                    {
                        "type": "publication",
                        "title": "Test publication avec scope_target team"
                    }
                ]
            }
            """

    Scenario: editable on a timeline action falls back to "all my scopes with ACTIONS feature" without ?scope=
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/je-mengage/timeline_feeds"
        Then the response status code should be 200
        And the JSON nodes should match:
            | hits[4].type     | action              |
            | hits[4].title    | Test action terrain |
            | hits[4].editable | true                |

    Scenario: editable on a timeline action stays false for users who are not the author
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/je-mengage/timeline_feeds"
        Then the response status code should be 200
        And the JSON nodes should match:
            | hits[4].type     | action              |
            | hits[4].title    | Test action terrain |
            | hits[4].editable | false               |

    Scenario: As a user without OAuth jemarche_app scope I cannot access timeline feeds
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/je-mengage/timeline_feeds"
        Then the response status code should be 403
