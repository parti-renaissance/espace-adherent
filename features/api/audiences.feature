@api
Feature:
  In order to see, create, edit and delete audiences
  As a logged-in user
  I should be able to access API audiences

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadClientData                  |
      | LoadGeoZoneData                 |
      | LoadAudienceData                |
      | LoadDistrictData                |
      | LoadReferentTagData             |
      | LoadReferentTagsZonesLinksData  |

  Scenario Outline: As a non logged-in user I can not manage audiences
    Given I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method  | url                                                     |
      | POST    | /api/v3/audiences                                       |
      | PUT     | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c  |
      | DELETE  | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c  |

  Scenario: As a logged-in user I can not create an audience if I have no rights
    Given I am logged with "carl999@example.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/audiences" with body:
    """
    {
      "type": "deputy"
    }
    """
    Then the response status code should be 403

  Scenario: As a logged-in user I can not create an audience if I have no rights for this audience type
    Given I am logged with "referent-child@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/audiences" with body:
    """
    {
      "type": "deputy"
    }
    """
    Then the response status code should be 403

  Scenario: As a logged-in user I can not create an audience with no data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/audiences" with body:
    """
    {
      "type": "deputy"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "type":"https://tools.ietf.org/html/rfc2616#section-10",
       "title":"An error occurred",
       "detail":"zone: Cette valeur ne doit pas \u00eatre vide.\nname: Cette valeur ne doit pas \u00eatre vide.",
       "violations":[
          {
             "propertyPath":"zone",
             "message":"Cette valeur ne doit pas être vide."
          },
          {
             "propertyPath":"name",
             "message":"Cette valeur ne doit pas être vide."
          }
       ]
    }
    """

  Scenario: As a logged-in user I can not create an audience with invalid data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/audiences" with body:
    """
    {
      "type": "deputy",
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
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "zone: La zone choisie ne fait pas partie des zones gérées.\nname: Cette valeur ne doit pas être vide.\nfirst_name: Vous devez saisir au maximum 50 caractères.\nlast_name: Vous devez saisir au maximum 50 caractères.\ngender: Ce sexe n'est pas valide.",
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
          "message": "Ce sexe n'est pas valide."
        }
      ]
    }
    """

  Scenario: As a logged-in user I can create an audience
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/audiences" with body:
    """
    {
      "type": "deputy",
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
       "name":"Nouvelle audience",
       "first_name":"Prénom",
       "last_name":"Nom",
       "gender":"female",
       "age_min":20,
       "age_max":45,
       "registered_since":"2017-06-29T00:00:00+02:00",
       "registered_until":"2021-04-29T00:00:00+02:00",
       "zone":{
          "uuid":"e3f0bf9d-906e-11eb-a875-0242ac150002",
          "code":"75-1",
          "name":"Paris (1)"
       },
       "is_committee_member":true,
       "is_certified":false,
       "has_email_subscription":false,
       "has_sms_subscription":true,
       "uuid": "@uuid@"
    }
    """

  Scenario: As a logged-in user I can edit an audience
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c" with body:
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
       "name":"Nouveau nom",
       "first_name":"Nouveau prénom",
       "last_name":"Nouveau nom",
       "gender":"female",
       "age_min":20,
       "age_max":45,
       "registered_since":"2018-08-28T00:00:00+02:00",
       "registered_until":"2021-06-28T00:00:00+02:00",
       "zone":{
          "uuid":"e3f0bf9d-906e-11eb-a875-0242ac150002",
          "code":"75-1",
          "name":"Paris (1)"
       },
       "is_committee_member":false,
       "is_certified":true,
       "has_email_subscription":false,
       "has_sms_subscription":true,
       "uuid":"f7ac8140-0a5b-4832-a5f4-47e661dc130c"
    }
    """

  Scenario: As a logged-in user with correct rights I can delete an audience
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "DELETE" request to "/api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c"
    Then the response status code should be 204

  Scenario Outline: As a non logged-in user I can not manage an audience
    When I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method | url                                                    |
      | PUT    | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c |
      | DELETE | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c |

  Scenario Outline: As a logged-in user with no correct rights I can not manage an audience
    Given I am logged with "<user>" via OAuth client "Data-Corner"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method | url                                                    | user                              |
      | PUT    | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | referent-child@en-marche-dev.fr   |
      | DELETE | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | referent-child@en-marche-dev.fr   |
      | PUT    | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | adherent-male-a@en-marche-dev.fr  |
      | DELETE | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | adherent-male-a@en-marche-dev.fr  |
      | PUT    | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | carl999@example.fr                |
      | DELETE | /api/v3/audiences/f7ac8140-0a5b-4832-a5f4-47e661dc130c | carl999@example.fr                |
