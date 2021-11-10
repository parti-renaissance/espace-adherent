@api
Feature:
  In order to see teams
  As a logged-in user
  I should be able to access API teams

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |
      | LoadClientData   |
      | LoadScopeData    |
      | LoadTeamData     |

  Scenario Outline: As a logged-in user without phoning team manager right I can not access teams endpoints
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Data-Corner"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403

    Examples:
      | method | url                                                                                                                            |
      | GET    | /api/v3/teams?scope=phoning_national_manager                                                                                   |
      | GET    | /api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=phoning_national_manager                                              |
      | GET    | /api/v3/adherents/autocomplete?q=petit&scope=phoning_national_manager                                                          |
      | GET    | /api/v3/adherents/autocomplete?q=petit&scope=phoning_national_manager                                                          |
      | PUT    | /api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=phoning_national_manager                                              |
      | PUT    | /api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/add-members?scope=phoning_national_manager                                  |
      | DELETE | /api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/members/918f07e5-676b-49c0-b76d-72ce01cb2404?scope=phoning_national_manager |

  Scenario: As a logged-in user with phoning team manager right I can get teams
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

  Scenario: As a logged-in user without phoning team manager right I can not create team
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/teams?scope=phoning_national_manager" with body:
    """
    {
      "name": "Troisième équipe de phoning"
    }
    """
    Then the response status code should be 403

  Scenario: As a logged-in user with phoning team manager right I can create team
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

  Scenario: As a logged-in user with phoning team manager right I can get a team
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
          "adherent_uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
          "first_name": "Lucie",
          "last_name": "Olivera",
          "registered_at": "2017-01-18T13:15:28+01:00",
          "postal_code": "75009"
        },
        {
          "adherent_uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
          "first_name": "Jacques",
          "last_name": "Picard",
          "registered_at": "2017-01-03T08:47:54+01:00",
          "postal_code": "75008"
        },
        {
          "adherent_uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
          "first_name": "Pierre",
          "last_name": "Kiroule",
          "registered_at": "2017-04-09T06:20:38+02:00",
          "postal_code": "10019"
        },
        {
          "adherent_uuid": "918f07e5-676b-49c0-b76d-72ce01cb2404",
          "first_name": "Député",
          "last_name": "PARIS I",
          "registered_at": "2017-06-01T09:26:31+02:00",
          "postal_code": "75008"
        }
      ]
    }
    """

  Scenario: As an anonymous I can not update a team
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=phoning_national_manager" with body:
    """
    {
      "name": "Equipe d'appel - IDF"
    }
    """
    Then the response status code should be 401

  Scenario: As a logged-in user with phoning team manager right I can update team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=phoning_national_manager" with body:
    """
    {
      "name": "Equipe d'appel - IDF"
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "name": "Equipe d'appel - IDF",
      "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
      "creator": "Admin",
      "members": [
        {
          "adherent_uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
          "first_name": "Lucie",
          "last_name": "Olivera",
          "registered_at": "2017-01-18T13:15:28+01:00",
          "postal_code": "75009"
        },
        {
          "adherent_uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
          "first_name": "Jacques",
          "last_name": "Picard",
          "registered_at": "2017-01-03T08:47:54+01:00",
          "postal_code": "75008"
        },
        {
          "adherent_uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
          "first_name": "Pierre",
          "last_name": "Kiroule",
          "registered_at": "2017-04-09T06:20:38+02:00",
          "postal_code": "10019"
        },
        {
          "adherent_uuid": "918f07e5-676b-49c0-b76d-72ce01cb2404",
          "first_name": "Député",
          "last_name": "PARIS I",
          "registered_at": "2017-06-01T09:26:31+02:00",
          "postal_code": "75008"
        }
      ]
    }
    """

  Scenario: As a logged-in user I get empty result when query value is null
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/adherents/autocomplete?q=&scope=phoning_national_manager"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    []
    """

  Scenario: As Anonymous user I can not search an adherent with autocomplete search
    When I send a "GET" request to "/api/v3/adherents/autocomplete?q=petit&scope=phoning_national_manager"
    Then the response status code should be 401

  Scenario: As a logged-in user I can search an adherent with autocomplete search
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/adherents/autocomplete?q=petit&scope=phoning_national_manager"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "registered_at": "@string@.isDateTime()",
        "uuid": "@uuid@",
        "first_name": "Adrien",
        "last_name": "Petit",
        "postal_code": "77000"
      },
      {
        "registered_at": "@string@.isDateTime()",
        "uuid": "@uuid@",
        "first_name": "Agathe",
        "last_name": "Petit",
        "postal_code": "77000"
      },
      {
        "registered_at": "@string@.isDateTime()",
        "uuid": "@uuid@",
        "first_name": "Étienne",
        "last_name": "Petit",
        "postal_code": "77000"
      }
    ]
    """

  Scenario: As a logged-in user with phoning team manager right I can add an adherent to a team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/add-members?scope=phoning_national_manager" with body:
    """
    [
      {
        "adherent_uuid": "acc73b03-9743-47d8-99db-5a6c6f55ad67"
      }
    ]
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "name": "Deuxième équipe de phoning",
      "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
      "creator": "Admin",
      "members": [
        {
          "adherent_uuid": "acc73b03-9743-47d8-99db-5a6c6f55ad67",
          "first_name": "Benjamin",
          "last_name": "Duroc",
          "registered_at": "2017-01-16T18:33:22+01:00",
          "postal_code": "13003"
        },
        {
          "adherent_uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
          "first_name": "Lucie",
          "last_name": "Olivera",
          "registered_at": "2017-01-18T13:15:28+01:00",
          "postal_code": "75009"
        },
        {
          "adherent_uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
          "first_name": "Jacques",
          "last_name": "Picard",
          "registered_at": "2017-01-03T08:47:54+01:00",
          "postal_code": "75008"
        },
        {
          "adherent_uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
          "first_name": "Pierre",
          "last_name": "Kiroule",
          "registered_at": "2017-04-09T06:20:38+02:00",
          "postal_code": "10019"
        },
        {
          "adherent_uuid": "918f07e5-676b-49c0-b76d-72ce01cb2404",
          "first_name": "Député",
          "last_name": "PARIS I",
          "registered_at": "2017-06-01T09:26:31+02:00",
          "postal_code": "75008"
        }
      ]
    }
    """

  Scenario: As an anonymous I can not remove an adherent from a team
    When I send a "DELETE" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/members/918f07e5-676b-49c0-b76d-72ce01cb2404?scope=phoning_national_manager"
    Then the response status code should be 401

  Scenario: As a logged-in user with phoning team manager right I can not remove an adherent who does not exist from a team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "DELETE" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/members/918f07e5-676b-49c0-b76d-17816bf2d819?scope=phoning_national_manager"
    Then the response status code should be 404

  Scenario: As a logged-in user with phoning team manager right I can remove an adherent from a team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "DELETE" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/members/918f07e5-676b-49c0-b76d-72ce01cb2404?scope=phoning_national_manager"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "name": "Deuxième équipe de phoning",
      "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
      "creator": "Admin",
      "members": [
        {
          "adherent_uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
          "first_name": "Lucie",
          "last_name": "Olivera",
          "registered_at": "2017-01-18T13:15:28+01:00",
          "postal_code": "75009"
        },
        {
          "adherent_uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
          "first_name": "Jacques",
          "last_name": "Picard",
          "registered_at": "2017-01-03T08:47:54+01:00",
          "postal_code": "75008"
        },
        {
          "adherent_uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
          "first_name": "Pierre",
          "last_name": "Kiroule",
          "registered_at": "2017-04-09T06:20:38+02:00",
          "postal_code": "10019"
        }
      ]
    }
    """

  Scenario: As a logged-in user with phoning team manager right I can not add an adherent twice
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/add-members?scope=phoning_national_manager" with body:
    """
    [
      {
        "adherent_uuid": "29461c49-6316-5be1-9ac3-17816bf2d819"
      }
    ]
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "name": "Deuxième équipe de phoning",
      "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
      "creator": "Admin",
      "members": [
        {
          "adherent_uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
          "first_name": "Lucie",
          "last_name": "Olivera",
          "registered_at": "2017-01-18T13:15:28+01:00",
          "postal_code": "75009"
        },
        {
          "adherent_uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
          "first_name": "Jacques",
          "last_name": "Picard",
          "registered_at": "2017-01-03T08:47:54+01:00",
          "postal_code": "75008"
        },
        {
          "adherent_uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
          "first_name": "Pierre",
          "last_name": "Kiroule",
          "registered_at": "2017-04-09T06:20:38+02:00",
          "postal_code": "10019"
        },
        {
          "adherent_uuid": "918f07e5-676b-49c0-b76d-72ce01cb2404",
          "first_name": "Député",
          "last_name": "PARIS I",
          "registered_at": "2017-06-01T09:26:31+02:00",
          "postal_code": "75008"
        }
      ]
    }
    """

  Scenario: As a DC referent I can get the team list filtered by the name
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/teams?name=Deuxi&scope=phoning_national_manager"
    Then the response status code should be 200
    And the JSON should be equal to:
   """
    {
      "metadata": {
        "total_items": 1,
        "items_per_page": 2,
        "count": 1,
        "current_page": 1,
        "last_page": 1
      },
      "items": [
        {
          "name": "Deuxième équipe de phoning",
          "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
          "members_count": 4,
          "creator": "Admin"
        }
      ]
    }
    """
