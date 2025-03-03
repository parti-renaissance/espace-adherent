@api
@renaissance
Feature:
    In order to see, create, edit and delete audiences
    As a logged-in user
    I should be able to access API audiences

    Scenario Outline: As a non logged-in user I can not manage audiences
        Given I send a "<method>" request to "<url>"
        Then the response status code should be 401

        Examples:
            | method | url                                                    |
            | POST   | /api/v3/audiences                                      |
            | GET    | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c |
            | PUT    | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c |
            | DELETE | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c |

    Scenario: As a logged-in user I can not create an audience if I have no rights
        Given I am logged with "carl999@example.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/audiences" with body:
            """
            {
                "scope": "deputy"
            }
            """
        Then the response status code should be 403

    Scenario: As a logged-in user I can not create an audience if I have no rights for this audience type
        Given I am logged with "referent-child@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/audiences" with body:
            """
            {
                "scope": "deputy"
            }
            """
        Then the response status code should be 403

    Scenario: As a logged-in user I can not create an audience with no data
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/audiences" with body:
            """
            {
                "scope": "deputy"
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "name",
                        "message": "Cette valeur ne doit pas être vide."
                    }
                ]
            }
            """

    Scenario: As a logged-in user I can not create an audience with invalid data
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/audiences" with body:
            """
            {
                "scope": "deputy",
                "first_name": "untrèslongprénomuntrèslongprénomuntrèslongprénomuntrèslongprénomuntrèslongprénomuntrèslongprénom",
                "last_name": "untrèslongnomuntrèslongnomuntrèslongnomuntrèslongnomuntrèslongnomuntrèslongnomuntrèslongnom",
                "gender": "invalid",
                "zone": "e3ef6700-906e-11eb-a875-0242ac150002"
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "zone",
                        "message": "La zone choisie ne fait pas partie des zones gérées."
                    },
                    {
                        "propertyPath": "name",
                        "message": "Cette valeur ne doit pas être vide."
                    },
                    {
                        "propertyPath": "first_name",
                        "message": "Vous devez saisir au maximum 50 caractères."
                    },
                    {
                        "propertyPath": "last_name",
                        "message": "Vous devez saisir au maximum 50 caractères."
                    },
                    {
                        "propertyPath": "gender",
                        "message": "Cette civilité n'est pas valide."
                    }
                ]
            }
            """

    Scenario: As a logged-in user I can create an audience
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/audiences" with body:
            """
            {
                "scope": "deputy",
                "name": "Nouvelle audience",
                "first_name": "Prénom",
                "last_name": "Nom",
                "gender": "female",
                "zone": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                "age_min": 20,
                "age_max": 45,
                "registered_since": "2017-06-29",
                "registered_until": "2021-04-29",
                "is_committee_member": true,
                "is_certified": false,
                "has_email_subscription": false,
                "has_sms_subscription": true
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "name": "Nouvelle audience",
                "first_name": "Prénom",
                "last_name": "Nom",
                "gender": "female",
                "age_min": 20,
                "age_max": 45,
                "registered_since": "2017-06-29T00:00:00+02:00",
                "registered_until": "2021-04-29T00:00:00+02:00",
                "zone": {
                    "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                    "code": "75-1",
                    "name": "Paris (1)"
                },
                "is_committee_member": true,
                "is_certified": false,
                "has_email_subscription": false,
                "has_sms_subscription": true,
                "uuid": "@uuid@"
            }
            """

    Scenario: As a logged-in user I can edit an audience
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c?scope=deputy" with body:
            """
            {
                "name": "Nouveau nom",
                "first_name": "Nouveau prénom",
                "last_name": "Nouveau nom",
                "gender": "female",
                "zone": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                "age_min": 20,
                "age_max": 45,
                "registered_since": "2018-08-28",
                "registered_until": "2021-06-28",
                "is_committee_member": false,
                "is_certified": true,
                "has_email_subscription": false,
                "has_sms_subscription": true
            }
            """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "name": "Nouveau nom",
                "first_name": "Nouveau prénom",
                "last_name": "Nouveau nom",
                "gender": "female",
                "age_min": 20,
                "age_max": 45,
                "registered_since": "2018-08-28T00:00:00+02:00",
                "registered_until": "2021-06-28T00:00:00+02:00",
                "zone": {
                    "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                    "code": "75-1",
                    "name": "Paris (1)"
                },
                "is_committee_member": false,
                "is_certified": true,
                "has_email_subscription": false,
                "has_sms_subscription": true,
                "uuid": "f7ac8140-0a5b-4832-a5f4-47e661dc130c"
            }
            """

    Scenario: As a logged-in user with correct rights I can delete an audience
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "DELETE" request to "/api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c?scope=deputy"
        Then the response status code should be 204

    Scenario Outline: As a logged-in user with no correct rights I can not manage an audience
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "<method>" request to "<url>"
        Then the response status code should be 403

        Examples:
            | method | url                                                    | user                             |
            | GET    | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | referent-child@en-marche-dev.fr  |
            | PUT    | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | referent-child@en-marche-dev.fr  |
            | DELETE | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | referent-child@en-marche-dev.fr  |
            | GET    | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | adherent-male-a@en-marche-dev.fr |
            | PUT    | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | adherent-male-a@en-marche-dev.fr |
            | DELETE | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | adherent-male-a@en-marche-dev.fr |
            | GET    | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | carl999@example.fr               |
            | PUT    | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | carl999@example.fr               |
            | DELETE | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | carl999@example.fr               |

    Scenario: As a logged-in user with correct rights, but no audience type, I can not get audiences
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/audiences"
        Then the response status code should be 403

    Scenario: As a logged-in referent I can not get deputy audiences
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/audiences?scope=deputy"
        Then the response status code should be 403

    Scenario: As a logged-in deputy I can get deputy audiences
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/audiences?scope=deputy"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "name": "Audience DEPUTY avec les paramètres nécessaires",
                    "uuid": "bd298079-f763-4c7a-9a8a-a243d01d0e31"
                },
                {
                    "name": "Audience DEPUTY avec tous les paramètres possibles",
                    "uuid": "f7ac8140-0a5b-4832-a5f4-47e661dc130c"
                }
            ]
            """

    Scenario: As a logged-in deputy I can get a candidate audience with all parameters
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c?scope=deputy"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "zone": {
                    "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                    "code": "75-1",
                    "name": "Paris (1)"
                },
                "name": "Audience DEPUTY avec tous les paramètres possibles",
                "first_name": "Julien",
                "last_name": "PREMIER",
                "gender": "male",
                "age_min": 18,
                "age_max": 42,
                "registered_since": "2017-07-01T00:00:00+02:00",
                "registered_until": "2021-07-01T00:00:00+02:00",
                "is_committee_member": true,
                "is_certified": false,
                "has_email_subscription": true,
                "has_sms_subscription": false,
                "uuid": "f7ac8140-0a5b-4832-a5f4-47e661dc130c"
            }
            """

    Scenario: As a logged-in deputy I can get a candidate audience with some parameters
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/audiences/bd298079-f763-4c7a-9a8a-a243d01d0e31?scope=deputy"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "zone": {
                    "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                    "code": "75-1",
                    "name": "Paris (1)"
                },
                "name": "Audience DEPUTY avec les paramètres nécessaires",
                "first_name": null,
                "last_name": null,
                "gender": null,
                "age_min": null,
                "age_max": null,
                "registered_since": null,
                "registered_until": null,
                "is_committee_member": null,
                "is_certified": null,
                "has_email_subscription": null,
                "has_sms_subscription": null,
                "uuid": "bd298079-f763-4c7a-9a8a-a243d01d0e31"
            }
            """
