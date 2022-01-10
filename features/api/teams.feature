@api
Feature:
  In order to see teams
  As a logged-in user
  I should be able to access API teams

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


  Scenario: As an anonymous I can not remove an adherent from a team
    When I send a "DELETE" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/members/918f07e5-676b-49c0-b76d-72ce01cb2404?scope=phoning_national_manager"
    Then the response status code should be 401

  Scenario: As a logged-in user with phoning team manager right I can not remove an adherent who does not exist from a team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "DELETE" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/members/918f07e5-676b-49c0-b76d-17816bf2d819?scope=phoning_national_manager"
    Then the response status code should be 404




















  Scenario: As a user granted with national scope, I can get the list of national teams only
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/teams?scope=phoning_national_manager"
    Then the response status code should be 200
    And the response should be in JSON
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
          "visibility": "national",
          "zone": null,
          "members_count": 4,
          "creator": "Admin"
        },
        {
          "name": "Première équipe de phoning",
          "uuid": "3deeb1f5-819e-4629-85a1-eb75c916ce2f",
          "visibility": "national",
          "zone": null,
          "members_count": 3,
          "creator": "Admin"
        }
      ]
    }
    """

  Scenario: As a user granted with national scope, I can create a national team
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/teams?scope=phoning_national_manager" with body:
    """
    {
      "name": "Nouvelle équipe nationale de phoning"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "name": "Nouvelle équipe nationale de phoning",
      "uuid": "@uuid@",
      "visibility": "national",
      "zone": null,
      "creator": "Député PARIS I",
      "members": []
    }
    """

  Scenario: As a user granted with national scope, I can update a national team
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=phoning_national_manager" with body:
    """
    {
      "name": "Equipe d'appel - IDF"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "name": "Equipe d'appel - IDF",
      "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
      "creator": "Admin",
      "visibility": "national",
      "zone": null,
      "members": "@array@.count(4)"
    }
    """

  Scenario: As a user granted with national scope, I can not create a local team
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/teams?scope=phoning_national_manager" with body:
    """
    {
      "name": "Nouvelle équipe locale de phoning",
      "zone": "e3f21338-906e-11eb-a875-0242ac150002"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "zone: Un rôle national ne peut pas définir de zone.",
      "violations": [
        {
          "propertyPath": "zone",
          "message": "Un rôle national ne peut pas définir de zone."
        }
      ]
    }
    """

  Scenario: As a user granted with national scope, I can not update a local team
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/teams/ba9ab5dd-c8da-4721-8acb-5a96e285aec3?scope=phoning_national_manager" with body:
    """
    {
      "name": "Equipe d'appel - IDF"
    }
    """
    Then the response status code should be 403

  Scenario: As a user granted with local scope, I can get the list of local teams in the zones I am manager of
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/teams?scope=referent"
    Then the response status code should be 200
    And the response should be in JSON
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
          "name": "Équipe locale de la ville de Lille (59350)",
          "uuid": "ba9ab5dd-c8da-4721-8acb-5a96e285aec3",
          "visibility": "local",
          "zone": {
            "code": "59350",
            "name": "Lille",
            "uuid": "e3f21338-906e-11eb-a875-0242ac150002"
          },
          "members_count": 1,
          "creator": "Admin"
        },
        {
          "name": "Équipe locale du département 92",
          "uuid": "c608c447-8c45-4ee7-b39c-7d0217d1c6db",
          "visibility": "local",
          "zone": {
            "code": "92",
            "name": "Hauts-de-Seine",
            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
          },
          "members_count": 1,
          "creator": "Admin"
        }
      ]
    }
    """

  Scenario: As a user granted with local scope, I can create a local team in a zone I am manager of
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/teams?scope=referent" with body:
    """
    {
      "name": "Nouvelle équipe locale de phoning dans le 92",
      "zone": "e3efe6fd-906e-11eb-a875-0242ac150002"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "name": "Nouvelle équipe locale de phoning dans le 92",
      "uuid": "@uuid@",
      "visibility": "local",
      "zone": {
        "code": "92",
        "name": "Hauts-de-Seine",
        "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
      },
      "creator": "Referent Referent",
      "members": []
    }
    """

  Scenario: As a user granted with local scope, I can update a local team in a zone I am manager of
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/teams/ba9ab5dd-c8da-4721-8acb-5a96e285aec3?scope=referent" with body:
    """
    {
      "name": "Equipe d'appel - 59"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "name": "Equipe d'appel - 59",
      "visibility": "local",
      "zone": {
        "uuid": "e3f21338-906e-11eb-a875-0242ac150002",
        "code": "59350",
        "name": "Lille"
      },
      "uuid": "ba9ab5dd-c8da-4721-8acb-5a96e285aec3",
      "creator": "Admin",
      "members": "@array@.count(1)"
    }
    """

  Scenario: As a user granted with local scope, I can not create a local team in a zone I am not manager of
    Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/teams?scope=referent" with body:
    """
    {
      "name": "Nouvelle équipe locale de phoning dans le 92",
      "zone": "e3efe6fd-906e-11eb-a875-0242ac150002"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "zone: La zone spécifiée n'est pas gérée par votre rôle.",
      "violations": [
        {
          "propertyPath": "zone",
          "message": "La zone spécifiée n'est pas gérée par votre rôle."
        }
      ]
    }
    """

  Scenario: As a user granted with local scope, I can not update a local team in a zone I am not manager of
    Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/teams/ba9ab5dd-c8da-4721-8acb-5a96e285aec3?scope=referent" with body:
    """
    {
      "name": "Equipe d'appel - 59"
    }
    """
    Then the response status code should be 403

  Scenario: As a user granted with local scope, I can not create a national team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/teams?scope=referent" with body:
    """
    {
      "name": "Nouvelle équipe nationale de phoning"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "zone: Veuillez spécifier une zone.",
      "violations": [
        {
          "propertyPath": "zone",
          "message": "Veuillez spécifier une zone."
        }
      ]
    }
    """

  Scenario: As a user granted with local scope, I can not update a national team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=referent" with body:
    """
    {
      "name": "Equipe d'appel - IDF"
    }
    """
    Then the response status code should be 403

  Scenario: As an anonymous user, I can not get the list of teams
    Given I send a "GET" request to "/api/v3/teams?scope=referent"
    Then the response status code should be 401

  Scenario: As an anonymous user, I can not create a team
    Given I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/teams?scope=referent" with body:
    """
    {
      "name": "Nouvelle équipe locale de phoning dans le 92",
      "zone": "e3efe6fd-906e-11eb-a875-0242ac150002"
    }
    """
    Then the response status code should be 401

  Scenario: As an anonymous user, I can not update a team
    Given I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/teams/ba9ab5dd-c8da-4721-8acb-5a96e285aec3?scope=referent" with body:
    """
    {
      "name": "Equipe d'appel - 59"
    }
    """
    Then the response status code should be 401

  Scenario: As a user granted with team feature, I can add a member to a team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=phoning_national_manager"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "name": "Deuxième équipe de phoning",
      "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
      "visibility": "national",
      "zone": null,
      "creator": "Admin",
      "members": "@array@.count(4)"
    }
    """

    When I add "Content-Type" header equal to "application/json"
    When I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/add-members?scope=phoning_national_manager" with body:
    """
    [
      {
        "adherent_uuid": "acc73b03-9743-47d8-99db-5a6c6f55ad67"
      }
    ]
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "name": "Deuxième équipe de phoning",
      "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
      "visibility": "national",
      "zone": null,
      "creator": "Admin",
      "members": "@array@.count(5)"
    }
    """
    And the JSON should be a superset of:
    """
    {
      "members": [
        {
          "adherent_uuid": "acc73b03-9743-47d8-99db-5a6c6f55ad67"
        }
      ]
    }
    """

  Scenario: As a user granted with team feature, I can not add the same member twice to the same team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    And I send a "GET" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=phoning_national_manager"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "name": "Deuxième équipe de phoning",
      "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
      "visibility": "national",
      "zone": null,
      "creator": "Admin",
      "members": "@array@.count(4)"
    }
    """
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
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "name": "Deuxième équipe de phoning",
      "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
      "visibility": "national",
      "zone": null,
      "creator": "Admin",
      "members": "@array@.count(4)"
    }
    """
    And the JSON should be a superset of:
    """
    {
      "members": [
        {
          "adherent_uuid": "29461c49-6316-5be1-9ac3-17816bf2d819"
        }
      ]
    }
    """

  Scenario: As a user granted with team feature, I should see validation errors when trying to add an adherent to a team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    And I add "Content-Type" header equal to "application/json"

    # Empty request
    When I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/add-members?scope=phoning_national_manager"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    "Request body should not be empty."
    """

    # Empty item
    When I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/add-members?scope=phoning_national_manager" with body:
    """
    [{}]
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://symfony.com/errors/validation",
      "title": "Validation Failed",
      "detail": "[0].adherent_uuid: Cette valeur ne doit pas être vide.",
      "violations": [
        {
          "propertyPath": "[0].adherent_uuid",
          "title": "Cette valeur ne doit pas être vide.",
          "parameters": {
            "{{ value }}": "null"
          },
          "type": "urn:uuid:c1051bb4-d103-4f74-8988-acbcafc7fdc3"
        }
      ]
    }
    """

    # Empty adherent UUID
    When I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/add-members?scope=phoning_national_manager" with body:
    """
    [
      {
        "adherent_uuid": null
      }
    ]
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://symfony.com/errors/validation",
      "title": "Validation Failed",
      "detail": "[0].adherent_uuid: Cette valeur ne doit pas être vide.",
      "violations": [
        {
          "propertyPath": "[0].adherent_uuid",
          "title": "Cette valeur ne doit pas être vide.",
          "parameters": {
            "{{ value }}": "null"
          },
          "type": "urn:uuid:c1051bb4-d103-4f74-8988-acbcafc7fdc3"
        }
      ]
    }
    """

    # Unknown adherent UUID
    When I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/add-members?scope=phoning_national_manager" with body:
    """
    [
      {
        "adherent_uuid": "c1051bb4-d103-4f74-8988-acbcafc7fdc3"
      }
    ]
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://symfony.com/errors/validation",
      "title": "Validation Failed",
      "detail": "[0].adherent_uuid: adherent.uuid.adherent_not_found",
      "violations": [
        {
          "propertyPath": "[0].adherent_uuid",
          "title": "adherent.uuid.adherent_not_found",
          "parameters": {
            "{{ value }}": "c1051bb4-d103-4f74-8988-acbcafc7fdc3"
          }
        }
      ]
    }
    """

  Scenario: As an anonymous user, I can not add a member to a team
    When I add "Content-Type" header equal to "application/json"
    When I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/add-members?scope=phoning_national_manager" with body:
    """
    [
      {
        "adherent_uuid": "acc73b03-9743-47d8-99db-5a6c6f55ad67"
      }
    ]
    """
    Then the response status code should be 401
@debug
  Scenario: As a user granted with team feature, I can remove a member from a team
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=phoning_national_manager"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "name": "Deuxième équipe de phoning",
      "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
      "visibility": "national",
      "zone": null,
      "creator": "Admin",
      "members": "@array@.count(4)"
    }
    """
    And the JSON should be a superset of:
    """
    {
      "members": [
        {
          "adherent_uuid": "29461c49-6316-5be1-9ac3-17816bf2d819"
        }
      ]
    }
    """

    When I send a "DELETE" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/members/29461c49-6316-5be1-9ac3-17816bf2d819?scope=phoning_national_manager"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "name": "Deuxième équipe de phoning",
      "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
      "visibility": "national",
      "zone": null,
      "creator": "Admin",
      "members": [
        {
          "adherent_uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
          "first_name": "Jacques",
          "last_name": "Picard",
          "registered_at": "@string@.isDateTime()",
          "postal_code": "75008"
        },
        {
          "adherent_uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
          "first_name": "Pierre",
          "last_name": "Kiroule",
          "registered_at": "@string@.isDateTime()",
          "postal_code": "10019"
        },
        {
          "adherent_uuid": "918f07e5-676b-49c0-b76d-72ce01cb2404",
          "first_name": "Député",
          "last_name": "PARIS I",
          "registered_at": "@string@.isDateTime()",
          "postal_code": "75008"
        }

      ]
    }
    """

  Scenario: As an anonymous user, I can not remove a member from a team
    When I send a "DELETE" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/members/918f07e5-676b-49c0-b76d-72ce01cb2404?scope=phoning_national_manager"
    Then the response status code should be 401

  Scenario: As a user granted with team feature, I can filter the team list by name
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/teams?name=Deuxi&scope=phoning_national_manager"
    Then the response status code should be 200
    And the response should be in JSON
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
          "visibility": "national",
          "zone": null,
          "members_count": 4,
          "creator": "Admin"
        }
      ]
    }
    """
