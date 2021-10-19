@api
Feature:
  In order to see phoning teams
  As a logged-in user
  I should be able to access API phoning teams

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadClientData                  |
      | LoadScopeData                   |
      | LoadTeamData                    |

  Scenario: As a logged-in user without phoning team manager right I can not get phoning teams
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/teams?scope=phoning_national_manager"
    Then the response status code should be 403

  Scenario: As a logged-in user with phoning team manager right I can get only phoning teams
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/teams?scope=phoning_national_manager"
    Then the response status code should be 200
    And the JSON should be equal to:
   """
    {
      "metadata": {
        "total_items": 2,
        "items_per_page": 2,
        "count": 2,
        "current_page": 1,
        "last_page": 1
      },
      "items": [
        {
          "name": "Deuxième équipe de phoning",
          "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
          "members_count": 4,
          "creator": "Admin"
        },
        {
          "name": "Première équipe de phoning",
          "uuid": "3deeb1f5-819e-4629-85a1-eb75c916ce2f",
          "members_count": 3,
          "creator": "Admin"
        }
      ]
    }
    """

  Scenario: As a logged-in user without phoning team manager right I can not create phoning team
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/teams?scope=phoning_national_manager" with body:
    """
    {
      "name": "Troisième équipe de phoning"
    }
    """
    Then the response status code should be 403

  Scenario: As a logged-in user with phoning team manager right I can create phoning team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/teams?scope=phoning_national_manager" with body:
    """
    {
      "name": "Troisième équipe de phoning"
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
      "name": "Troisième équipe de phoning",
      "uuid": "@string@",
      "creator": "Referent R.",
      "members": []
    }
    """

  Scenario: As a logged-in user with phoning team manager right I can get a phoning team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=phoning_national_manager"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "name": "Deuxième équipe de phoning",
      "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
      "creator": "Admin",
      "members": [
        {
          "uuid": "3b05dde9-acd0-43b7-83a5-a67cda9a7946",
          "first_name": "Lucie",
          "last_name": "Olivera",
          "registred_at": "2017-01-18T13:15:28+01:00",
          "postal_code": "75009"
        },
        {
          "uuid": "5a0d85bf-2c66-4bc3-aa29-c07b03951bc4",
          "first_name": "Jacques",
          "last_name": "Picard",
          "registred_at": "2017-01-03T08:47:54+01:00",
          "postal_code": "75008"
        },
        {
          "uuid": "a33fa2f6-e7ee-4755-a399-bfc93015529e",
          "first_name": "Pierre",
          "last_name": "Kiroule",
          "registred_at": "2017-04-09T06:20:38+02:00",
          "postal_code": "10019"
        },
        {
          "uuid": "76dd7e44-1a7e-4d2f-bdd8-018690ac5211",
          "first_name": "Député",
          "last_name": "PARIS I",
          "registred_at": "2017-06-01T09:26:31+02:00",
          "postal_code": "75008"
        }
      ]
    }
    """
