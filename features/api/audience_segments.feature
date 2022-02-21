@api
Feature:
  In order to see audience segments
  I should be able to access API of audience segments

  Scenario Outline: As a non logged-in user I can not manage audience segments
    Given I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method  | url                                                             |
      | POST    | /api/v3/audience-segments                                       |
      | GET     | /api/v3/audience-segments/f6c36dd7-0517-4caf-ba6f-ec6822f2ec12  |
      | PUT     | /api/v3/audience-segments/f6c36dd7-0517-4caf-ba6f-ec6822f2ec12  |
      | DELETE  | /api/v3/audience-segments/f6c36dd7-0517-4caf-ba6f-ec6822f2ec12  |

  Scenario Outline: As a logged-in user with no correct rights I can not audience segments
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method  | url                                                             |
      | GET     | /api/v3/audience-segments/830d230f-67fb-4217-9986-1a3ed7d3d5e7  |
      | PUT     | /api/v3/audience-segments/830d230f-67fb-4217-9986-1a3ed7d3d5e7  |
      | DELETE  | /api/v3/audience-segments/830d230f-67fb-4217-9986-1a3ed7d3d5e7  |

  Scenario: As a logged-in user I cannot get audience segments
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/audience-segments"
    Then the response status code should be 404

  Scenario: As a logged-in user I can get an audience segment
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/audience-segments/f6c36dd7-0517-4caf-ba6f-ec6822f2ec12"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "filter": {
        "is_certified": false,
        "scope": "deputy",
        "zone": {
          "uuid": "e3f2c4a0-906e-11eb-a875-0242ac150002",
          "code": "92024",
          "name": "Clichy"
        },
        "is_committee_member": null,
        "gender": "male",
        "age_min": 20,
        "age_max": 30,
        "first_name": null,
        "last_name": null,
        "registered_since": null,
        "registered_until": null
      },
      "synchronized": false,
      "recipient_count": null,
      "uuid": "f6c36dd7-0517-4caf-ba6f-ec6822f2ec12"
    }
    """

  Scenario: As a logged-in user with no correct rights I cannot create an audience segment
    Given I am logged with "michel.vasseur@example.ch" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/audience-segments" with body:
    """
    {
        "filter": {
          "scope": "referent",
          "first_name": "Pierre",
          "last_name": "Dupond",
          "gender": "male",
          "zone": "e3f0bf9d-906e-11eb-a875-0242ac150002",
          "age_min": 25,
          "age_max": 35,
          "registered_since": "2017-06-21",
          "registered_until": "2021-04-29",
          "is_committee_member": false,
          "is_certified": false
        }
    }
    """
    Then the response status code should be 403

  Scenario: As a logged-in user I cannot create an audience segment, if filter scope is not authorized
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/audience-segments?scope=referent" with body:
    """
    {
        "filter": {
          "scope": "referent",
          "first_name": "Pierre",
          "last_name": "Dupond",
          "gender": "male",
          "zone": "e3f0bf9d-906e-11eb-a875-0242ac150002",
          "age_min": 25,
          "age_max": 35,
          "registered_since": "2017-06-21",
          "registered_until": "2021-04-29",
          "is_committee_member": false,
          "is_certified": false
        }
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "type":"https://tools.ietf.org/html/rfc2616#section-10",
       "title":"An error occurred",
       "detail":"filter.scope.type: Le scope n'est pas autoris\u00e9",
       "violations":[
          {
             "propertyPath":"filter.scope.type",
             "message":"Le scope n'est pas autorisé"
          }
       ]
    }
    """

  Scenario: As a logged-in user I cannot create an audience segment with no data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/audience-segments" with body:
    """
    {}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "filter: Cette valeur ne doit pas être nulle.",
      "violations": [
        {
          "propertyPath": "filter",
          "message": "Cette valeur ne doit pas être nulle."
        }
      ]
    }
    """

  Scenario: As a logged-in user I can create an audience segment
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/audience-segments?scope=deputy" with body:
    """
    {
        "filter": {
          "scope": "deputy",
          "first_name": "Pierre",
          "last_name": "Dupond",
          "gender": "male",
          "zone": "e3f0bf9d-906e-11eb-a875-0242ac150002",
          "age_min": 25,
          "age_max": 35,
          "registered_since": "2017-06-21",
          "registered_until": "2021-04-29",
          "is_committee_member": true,
          "is_certified": false
        }
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
      "filter": {
        "is_certified": false,
        "scope": "deputy",
        "zone": {
          "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
          "code": "75-1",
          "name": "Paris (1)"
        },
        "is_committee_member": true,
        "gender": "male",
        "age_min": 25,
        "age_max": 35,
        "first_name": "Pierre",
        "last_name": "Dupond",
        "registered_since": "2017-06-21T00:00:00+02:00",
        "registered_until": "2021-04-29T00:00:00+02:00"
      },
      "synchronized": false,
      "recipient_count": null,
      "uuid": "@uuid@"
    }
    """

  Scenario Outline: As a (delegated) referent I can create an audience segment
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/audience-segments?scope=<scope>" with body:
    """
    {
        "filter": {
          "scope": "referent",
          "zone": "e3efe6fd-906e-11eb-a875-0242ac150002",
          "is_certified": false
        }
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "filter": {
            "is_certified": false,
            "zone": {
                "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                "code": "92",
                "name": "Hauts-de-Seine"
            },
            "scope": "referent",
            "gender": null,
            "age_min": null,
            "age_max": null,
            "first_name": null,
            "last_name": null,
            "registered_since": null,
            "registered_until": null,
            "is_committee_member": true
        },
        "uuid": "@uuid@",
        "recipient_count": null,
        "synchronized": false
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario: As a logged-in user I can edit my segment
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/audience-segments/830d230f-67fb-4217-9986-1a3ed7d3d5e7?scope=referent" with body:
    """
    {
        "filter": {
          "scope": "referent",
          "first_name": "Nouveau prénom",
          "last_name": "Nouveau nom",
          "gender": "male",
          "zone": "e3f21338-906e-11eb-a875-0242ac150002",
          "age_min": 25,
          "age_max": 35,
          "registered_since": "2018-01-01",
          "registered_until": "2019-12-12",
          "is_committee_member": true,
          "is_certified": false
        }
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "filter": {
        "is_certified": false,
        "scope": "referent",
        "zone": {
          "uuid": "e3f21338-906e-11eb-a875-0242ac150002",
          "code": "59350",
          "name": "Lille"
        },
        "is_committee_member": true,
        "gender": "male",
        "age_min": 25,
        "age_max": 35,
        "first_name": "Nouveau prénom",
        "last_name": "Nouveau nom",
        "registered_since": "2018-01-01T00:00:00+01:00",
        "registered_until": "2019-12-12T00:00:00+01:00"
      },
      "synchronized": false,
      "recipient_count": null,
      "uuid": "830d230f-67fb-4217-9986-1a3ed7d3d5e7"
    }
    """

  Scenario: As a logged-in user I can delete my segment
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "DELETE" request to "/api/v3/audience-segments/f6c36dd7-0517-4caf-ba6f-ec6822f2ec12"
    Then the response status code should be 204

  Scenario Outline: As a logged-in user I cannot manage not my audience segment
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method  | url                                                             |
      | GET     | /api/v3/audience-segments/830d230f-67fb-4217-9986-1a3ed7d3d5e7  |
      | PUT     | /api/v3/audience-segments/830d230f-67fb-4217-9986-1a3ed7d3d5e7  |
      | DELETE  | /api/v3/audience-segments/830d230f-67fb-4217-9986-1a3ed7d3d5e7  |
