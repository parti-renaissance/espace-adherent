@api
Feature:
  In order to see my teams
  As a logged-in user
  I should be able to access API my teams

  Scenario Outline: As Anonymous user I cannot access teams endpoints
    When I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method | url                                                          |
      | GET    | /api/v3/my_teams                                             |
      | POST   | /api/v3/my_teams                                             |
      | POST   | /api/v3/my_team_members                                      |
      | PUT    | /api/v3/my_team_members/d11d6ddd-dfba-4972-97b2-4c0bdf289559 |
      | GET    | /api/v3/my_team_members/d11d6ddd-dfba-4972-97b2-4c0bdf289559 |
      | DELETE | /api/v3/my_team_members/d11d6ddd-dfba-4972-97b2-4c0bdf289559 |

  Scenario Outline: As a logged-in user without correct right I cannot access teams endpoints
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403

    Examples:
      | method | url                                                                          |
      | GET    | /api/v3/my_teams?scope=referent                                              |
      | POST   | /api/v3/my_teams?scope=referent                                              |
      | GET    | /api/v3/my_team_members/d11d6ddd-dfba-4972-97b2-4c0bdf289559?scope=referent  |
      | DELETE | /api/v3/my_team_members/d11d6ddd-dfba-4972-97b2-4c0bdf289559?scope=referent  |

  Scenario: As a referent I cannot create my team if no scope
    Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "POST" request to "/api/v3/my_teams"
    Then the response status code should be 403

  Scenario: As a referent I cannot create my team for the scope I don't have
    Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "POST" request to "/api/v3/my_teams?scope=correspondent"
    Then the response status code should be 403

  Scenario: As a referent I can create my team
    Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "POST" request to "/api/v3/my_teams?scope=referent"
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "uuid": "@uuid@"
    }
    """

  Scenario: As a referent I will get my team when I want to create it, but it already exists
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "POST" request to "/api/v3/my_teams?scope=referent"
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "uuid": "7fab9d6c-71a1-4257-b42b-c6b9b2350a26"
    }
    """

  Scenario: As a referent I cannot add a new member in my team with invalid data
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/my_team_members?scope=referent" with body:
    """
    {
        "team": "7fab9d6c-71a1-4257-b42b-c6b9b2350a26",
        "adherent": "7dd297ad-a84c-4bbd-9fd2-d1152ebc3044",
        "role": "invalid",
        "scope_features": [
          "my_team",
          "mobile_app"
        ]
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "adherent: Ce militant ne peut pas être ajouter à l'équipe.\nadherent: Le militant choisi ne fait pas partie de la zone géographique que vous gérez.\nrole: Ce poste n'est pas valide.\nscope_features: Une ou plusieurs des accès délégués ne sont pas valides.",
        "violations": [
            {
                "propertyPath": "adherent",
                "message": "Ce militant ne peut pas être ajouter à l'équipe."
            },
            {
                "message": "Le militant choisi ne fait pas partie de la zone géographique que vous gérez.",
                "propertyPath": "adherent"
            },
            {
                "propertyPath": "role",
                "message": "Ce poste n'est pas valide."
            },
            {
                "propertyPath": "scope_features",
                "message": "Une ou plusieurs des accès délégués ne sont pas valides."
            }
        ]
    }
    """

  Scenario: As a correspondent I cannot add a new member in my team with invalid data (no user and not correspondent features)
    Given I am logged with "je-mengage-user-1@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/my_team_members?scope=correspondent" with body:
    """
    {
        "team": "17921a6c-cf1c-4b49-9aac-06bd3913c3f7",
        "role": "compliance_and_finance_manager",
        "scope_features": [
          "team",
          "phoning_campaign",
          "news",
          "survey"
        ]
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "adherent: Veuillez sélectionner un militant.\nscope_features: Vous pouvez déléguer que les accès que vous possédez.",
        "violations": [
            {
                "propertyPath": "adherent",
                "message": "Veuillez sélectionner un militant."
            },
            {
                "propertyPath": "scope_features",
                "message": "Vous pouvez déléguer que les accès que vous possédez."
            }
        ]
    }
    """

  Scenario: As a referent I cannot add a new member in my team, if the user is already in my team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/my_team_members?scope=referent" with body:
    """
    {
        "team": "7fab9d6c-71a1-4257-b42b-c6b9b2350a26",
        "adherent": "b4219d47-3138-5efd-9762-2ef9f9495084",
        "role": "mobilization_manager",
        "scope_features": [
          "contacts",
          "messages"
        ]
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "adherent: Le militant fait déjà partie de cette équipe.",
        "violations": [
            {
                "propertyPath": "adherent",
                "message": "Le militant fait déjà partie de cette équipe."
            }
        ]
    }
    """

  Scenario: As a referent I cannot add a new member in my team, if the user is not in my managed zone
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/my_team_members?scope=referent" with body:
    """
    {
        "team": "7fab9d6c-71a1-4257-b42b-c6b9b2350a26",
        "adherent": "2f69db3c-ecd7-4a8a-bd23-bb4c9cfd70cf",
        "role": "mobilization_manager",
        "scope_features": [
          "contacts",
          "messages"
        ]
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "adherent: Le militant choisi ne fait pas partie de la zone géographique que vous gérez.",
        "violations": [
            {
                "propertyPath": "adherent",
                "message": "Le militant choisi ne fait pas partie de la zone géographique que vous gérez."
            }
        ]
    }
    """

  Scenario: As a referent I can add a new member in my team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/my_team_members?scope=referent" with body:
    """
    {
        "team": "7fab9d6c-71a1-4257-b42b-c6b9b2350a26",
        "adherent": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
        "role": "mobilization_manager",
        "scope_features": [
          "contacts",
          "messages"
        ]
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "team": {
            "uuid": "7fab9d6c-71a1-4257-b42b-c6b9b2350a26"
        },
        "adherent": {
            "first_name": "Francis",
            "last_name": "Brioul",
            "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4"
        },
        "role": "mobilization_manager",
        "scope_features": [
            "contacts",
            "messages"
        ],
        "uuid": "@uuid@"
    }
    """

  Scenario: As a referent I can edit a member in my team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/my_team_members/d11d6ddd-dfba-4972-97b2-4c0bdf289559?scope=referent" with body:
    """
    {
        "adherent": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
        "role": "logistics_manager",
        "scope_features": [
          "events"
        ]
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "team": {
            "uuid": "7fab9d6c-71a1-4257-b42b-c6b9b2350a26"
        },
        "adherent": {
            "first_name": "Francis",
            "last_name": "Brioul",
            "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4"
        },
        "role": "logistics_manager",
        "scope_features": [
            "events"
        ],
        "uuid": "d11d6ddd-dfba-4972-97b2-4c0bdf289559"
    }
    """

  Scenario: As a referent I can get a member of my team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/v3/my_team_members/d11d6ddd-dfba-4972-97b2-4c0bdf289559?scope=referent"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "team": {
            "uuid": "7fab9d6c-71a1-4257-b42b-c6b9b2350a26"
        },
        "adherent": {
            "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
            "first_name": "Gisele",
            "last_name": "Berthoux"
        },
        "role": "communication_manager",
        "scope_features": [
            "contacts",
            "messages",
            "events"
        ],
        "uuid": "d11d6ddd-dfba-4972-97b2-4c0bdf289559"
    }
    """

  Scenario: As a referent I can delete a member of my team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "DELETE" request to "/api/v3/my_team_members/d11d6ddd-dfba-4972-97b2-4c0bdf289559?scope=referent"
    Then the response status code should be 204

  Scenario: As a referent I cannot delete a member of not my team
    Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "DELETE" request to "/api/v3/my_team_members/d11d6ddd-dfba-4972-97b2-4c0bdf289559?scope=referent"
    Then the response status code should be 403

  Scenario: As a referent I can get my team with members
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/v3/my_teams?scope=referent"
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
                "scope": "referent",
                "members": [
                    {
                        "adherent": {
                            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                            "first_name": "Jacques",
                            "last_name": "Picard"
                        },
                        "role": "mobilization_manager",
                        "scope_features": [
                            "contacts",
                            "events"
                        ],
                        "uuid": "7e82bb82-4b1e-4244-b484-7a51301df420"
                    },
                    {
                        "adherent": {
                            "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                            "first_name": "Gisele",
                            "last_name": "Berthoux"
                        },
                        "role": "communication_manager",
                        "scope_features": [
                            "contacts",
                            "messages",
                            "events"
                        ],
                        "uuid": "d11d6ddd-dfba-4972-97b2-4c0bdf289559"
                    },
                    {
                        "adherent": {
                            "first_name": "Bob",
                            "last_name": "Senateur (59)",
                            "uuid": "021268fe-d4b3-44a7-bce9-c001191249a7"
                        },
                        "role": "mobilization_manager",
                        "scope_features": [
                            "dashboard",
                            "mobile_app",
                            "contacts",
                            "messages",
                            "events",
                            "news",
                            "elections",
                            "pap",
                            "team",
                            "phoning_campaign",
                            "survey"
                        ],
                        "uuid": "e0da56db-c4c6-4aa4-ad8d-7e9505dfdd93"
                    }
                ],
                "uuid": "7fab9d6c-71a1-4257-b42b-c6b9b2350a26"
            }
        ]
    }
    """

  Scenario: As a referent I get an empty list of my team members, if I doesn't have my team
    Given I am logged with "referent-child@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/v3/my_teams?scope=referent"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 0,
            "items_per_page": 2,
            "count": 0,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
        ]
    }
    """
