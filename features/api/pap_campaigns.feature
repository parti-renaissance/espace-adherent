@api
Feature:
  In order to see PAP campaigns
  As a non logged-in user
  I should be able to access API PAP campaigns

  Scenario Outline: As a non logged-in user I cannot get and manage PAP campaigns
    Given I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method  | url                                                         |
      | GET     | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9  |
      | GET     | /api/v3/pap_campaigns                                       |
      | POST    | /api/v3/pap_campaigns                                       |
      | PUT     | /api/v3/pap_campaigns/9ba6b743-5018-4358-bdc0-eb2094010beb  |

  Scenario Outline: As a JeMarche App user I can not get not active PAP campaigns
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMengage Mobile"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 404
    Examples:
      | method  | url                                                           |
      | GET     | /api/v3/pap_campaigns/932d67d1-2da6-4695-82f6-42afc20f2e41    |
      | GET     | /api/v3/pap_campaigns/9ba6b743-5018-4358-bdc0-eb2094010beb    |

  Scenario Outline: As a user with no correct rights I can not create or edit PAP campaign
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMengage Mobile"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method  | url                                                                   |
      # not mine
      | PUT     | /api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da   |
      # active
      | PUT     | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9            |

  Scenario Outline: As a logged-in user with no PAP user role I cannot get and manage PAP campaigns
    Given I am logged with "deputy-75-2@en-marche-dev.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    And I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method  | url                                                                       |
      | GET     | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9                |
      | PUT     | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9                |
      | GET     | /api/v3/pap_campaigns                                                     |
      | GET     | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/survey         |
      | GET     | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/survey-config  |
      | GET     | /api/v3/pap_campaigns/tutorial                                            |

  Scenario Outline: As a logged-in user with no correct rights I cannot get PAP campaigns
    Given I am logged with "benjyd@aol.com" via OAuth client "JeMengage Web"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method | url                                                                                                    |
      | GET    | /api/v3/pap_campaigns?scope=pap_national_manager                                                       |
      | GET    | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9?scope=pap_national_manager                  |
      | GET    | /api/v3/pap_campaigns/kpi?scope=pap_national_manager                                                   |
      | GET    | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/replies?scope=phoning_national_manager      |
      | GET    | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/questioners?scope=phoning_national_manager  |

  Scenario: As a JeMarche App user I cannot update not my PAP campaign
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    When I send a "PUT" request to "/api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da"
    Then the response status code should be 403

  Scenario: As a user granted with national scope, I can get the list of national campaigns only
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns?scope=pap_national_manager"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 5,
        "items_per_page": 2,
        "count": 2,
        "current_page": 1,
        "last_page": 3
      },
      "items": [
        {
          "begin_at": "@string@.isDateTime()",
          "brief": "**Campagne** de 10 jours suivants",
          "finish_at": "@string@.isDateTime()",
          "goal": 600,
          "nb_addresses": 4,
          "nb_surveys": 3,
          "nb_visited_doors": 5,
          "nb_voters": 7,
          "title": "Campagne de 10 jours suivants",
          "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
          "visibility": "national",
          "zone": null
        },
        {
          "begin_at": "@string@.isDateTime()",
          "brief": "**Campagne** de 5 jours suivants",
          "finish_at": "@string@.isDateTime()",
          "goal": 500,
          "nb_addresses": 4,
          "nb_surveys": 0,
          "nb_visited_doors": 1,
          "nb_voters": 7,
          "title": "Campagne de 5 jours suivants",
          "uuid": "1c67b6bd-6da9-4a72-a266-813c419e7024",
          "visibility": "national",
          "zone": null
        }
      ]
    }
    """

  Scenario: As a user granted with national scope, I can create a national campaign
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaigns?scope=pap_national_manager" with body:
    """
    {
      "title": "Nouvelle campagne PAP",
      "brief": "**NOUVEAU**",
      "goal": 200,
      "begin_at": "2022-05-01 00:00:00",
      "finish_at": "2022-05-31 00:00:00",
      "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "title": "Nouvelle campagne PAP",
      "brief": "**NOUVEAU**",
      "goal": 200,
      "begin_at": "2022-05-01T00:00:00+02:00",
      "finish_at": "2022-05-31T00:00:00+02:00",
      "survey": {
        "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
      },
      "uuid": "@uuid@",
      "visibility": "national",
      "zone": null
    }
    """

  Scenario: As a user granted with national scope, I can update a national campaign
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9?scope=pap_national_manager" with body:
    """
    {
      "title": "NOUVEAU Campagne de 10 jours suivants",
      "brief": "NOUVEAU **Campagne** de 10 jours suivants",
      "goal": 1000,
      "begin_at": "2022-04-01 00:00:00",
      "finish_at": "2022-04-30 00:00:00",
      "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "title": "NOUVEAU Campagne de 10 jours suivants",
      "brief": "NOUVEAU **Campagne** de 10 jours suivants",
      "goal": 1000,
      "begin_at": "2022-04-01T00:00:00+02:00",
      "finish_at": "2022-04-30T00:00:00+02:00",
      "survey": {
          "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
      },
      "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
      "visibility": "national",
      "zone": null
    }
    """

  Scenario: As a user granted with national scope, I can not create a local campaign
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaigns?scope=pap_national_manager" with body:
    """
    {
      "title": "Nouvelle campagne PAP",
      "brief": "**NOUVEAU**",
      "goal": 200,
      "begin_at": "2022-05-01 00:00:00",
      "finish_at": "2022-05-31 00:00:00",
      "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
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

  Scenario: As a user granted with national scope, I can not update a local campaign
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaigns/31f24b6c-0884-461a-af34-dbbb7b1276ab?scope=pap_national_manager" with body:
    """
    {
      "title": "**NOUVEAU** Campagne de 10 jours suivants"
    }
    """
    Then the response status code should be 403

  Scenario Outline: As a user granted with local scope, I can get the list of national and local campaigns in the zones I am manager of
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns?scope=<scope>&page_size=10"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 7,
            "items_per_page": 10,
            "count": 7,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "title": "Campagne de 10 jours suivants",
                "brief": "**Campagne** de 10 jours suivants",
                "goal": 600,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                "visibility": "national",
                "zone": null,
                "nb_surveys": 3,
                "nb_visited_doors": 5,
                "nb_addresses": 4,
                "nb_voters": 7
            },
            {
                "title": "Campagne de 5 jours suivants",
                "brief": "**Campagne** de 5 jours suivants",
                "goal": 500,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "1c67b6bd-6da9-4a72-a266-813c419e7024",
                "visibility": "national",
                "zone": null,
                "nb_surveys": 0,
                "nb_visited_doors": 1,
                "nb_addresses": 4,
                "nb_voters": 7
            },
            {
                "title": "Campagne dans 10 jours",
                "brief": "### Campagne dans 10 jours",
                "goal": 400,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "63460047-c81a-44b9-aec9-152ecf58df93",
                "visibility": "national",
                "zone": null,
                "nb_surveys": 0,
                "nb_visited_doors": 0,
                "nb_addresses": 4,
                "nb_voters": 7
            },
            {
                "title": "Campagne dans 20 jours",
                "brief": "### Campagne dans 20 jours",
                "goal": 400,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "932d67d1-2da6-4695-82f6-42afc20f2e41",
                "visibility": "national",
                "zone": null,
                "nb_surveys": 0,
                "nb_visited_doors": 0,
                "nb_addresses": 4,
                "nb_voters": 7
            },
            {
                "title": "Campagne terminée",
                "brief": null,
                "goal": 100,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "9ba6b743-5018-4358-bdc0-eb2094010beb",
                "visibility": "national",
                "zone": null,
                "nb_surveys": 0,
                "nb_visited_doors": 1,
                "nb_addresses": 4,
                "nb_voters": 7
            },
            {
                "title": "Campagne locale du département 92",
                "brief": null,
                "goal": 100,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "e3c6e83f-7471-4e8f-b348-6c2eb26723ce",
                "visibility": "local",
                "zone": {
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                    "code": "92",
                    "name": "Hauts-de-Seine"
                },
                "nb_surveys": 0,
                "nb_visited_doors": 1,
                "nb_addresses": 1,
                "nb_voters": 0
            },
            {
                "title": "Campagne locale de la ville de Lille (59350)",
                "brief": null,
                "goal": 100,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "31f24b6c-0884-461a-af34-dbbb7b1276ab",
                "visibility": "local",
                "zone": {
                    "uuid": "e3f21338-906e-11eb-a875-0242ac150002",
                    "code": "59350",
                    "name": "Lille"
                },
                "nb_surveys": 0,
                "nb_visited_doors": 0,
                "nb_addresses": 0,
                "nb_voters": 0
            }
        ]
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can filter campaigns by visibility
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns?scope=<scope>&page_size=10&visibility=local"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 2,
            "items_per_page": 10,
            "count": 2,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "title": "Campagne locale du département 92",
                "brief": null,
                "goal": 100,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "e3c6e83f-7471-4e8f-b348-6c2eb26723ce",
                "visibility": "local",
                "zone": {
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                    "code": "92",
                    "name": "Hauts-de-Seine"
                },
                "nb_surveys": 0,
                "nb_visited_doors": 1,
                "nb_addresses": 1,
                "nb_voters": 0
            },
            {
                "title": "Campagne locale de la ville de Lille (59350)",
                "brief": null,
                "goal": 100,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "31f24b6c-0884-461a-af34-dbbb7b1276ab",
                "visibility": "local",
                "zone": {
                    "uuid": "e3f21338-906e-11eb-a875-0242ac150002",
                    "code": "59350",
                    "name": "Lille"
                },
                "nb_surveys": 0,
                "nb_visited_doors": 0,
                "nb_addresses": 0,
                "nb_voters": 0
            }
        ]
    }
    """

    When I send a "GET" request to "/api/v3/pap_campaigns?scope=<scope>&page_size=10&visibility=national"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 5,
            "items_per_page": 10,
            "count": 5,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "title": "Campagne de 10 jours suivants",
                "brief": "**Campagne** de 10 jours suivants",
                "goal": 600,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                "visibility": "national",
                "zone": null,
                "nb_surveys": 3,
                "nb_visited_doors": 5,
                "nb_addresses": 4,
                "nb_voters": 7
            },
            {
                "title": "Campagne de 5 jours suivants",
                "brief": "**Campagne** de 5 jours suivants",
                "goal": 500,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "1c67b6bd-6da9-4a72-a266-813c419e7024",
                "visibility": "national",
                "zone": null,
                "nb_surveys": 0,
                "nb_visited_doors": 1,
                "nb_addresses": 4,
                "nb_voters": 7
            },
            {
                "title": "Campagne dans 10 jours",
                "brief": "### Campagne dans 10 jours",
                "goal": 400,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "63460047-c81a-44b9-aec9-152ecf58df93",
                "visibility": "national",
                "zone": null,
                "nb_surveys": 0,
                "nb_visited_doors": 0,
                "nb_addresses": 4,
                "nb_voters": 7
            },
            {
                "title": "Campagne dans 20 jours",
                "brief": "### Campagne dans 20 jours",
                "goal": 400,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "932d67d1-2da6-4695-82f6-42afc20f2e41",
                "visibility": "national",
                "zone": null,
                "nb_surveys": 0,
                "nb_visited_doors": 0,
                "nb_addresses": 4,
                "nb_voters": 7
            },
            {
                "title": "Campagne terminée",
                "brief": null,
                "goal": 100,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "9ba6b743-5018-4358-bdc0-eb2094010beb",
                "visibility": "national",
                "zone": null,
                "nb_surveys": 0,
                "nb_visited_doors": 1,
                "nb_addresses": 4,
                "nb_voters": 7
            }
        ]
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can get a local campaign in a zone I am manager of
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns/e3c6e83f-7471-4e8f-b348-6c2eb26723ce?scope=<scope>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "title": "Campagne locale du département 92",
      "brief": null,
      "goal": 100,
      "begin_at": "@string@.isDateTime()",
      "finish_at": "@string@.isDateTime()",
      "uuid": "e3c6e83f-7471-4e8f-b348-6c2eb26723ce",
      "visibility": "local",
      "zone": {
        "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
        "code": "92",
        "name": "Hauts-de-Seine"
      },
      "nb_surveys": 0,
      "nb_visited_doors": 1,
      "nb_addresses": 1,
      "nb_voters": 0,
      "nb_collected_contacts": 0,
      "nb_contact_later": 0,
      "nb_door_open": 0,
      "nb_to_join": 0,
      "average_visit_time": 0
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can create a local campaign in a zone I am manager of
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaigns?scope=<scope>" with body:
    """
    {
      "title": "Nouvelle campagne PAP",
      "brief": "**NOUVEAU**",
      "goal": 200,
      "begin_at": "2022-05-01 00:00:00",
      "finish_at": "2022-05-31 00:00:00",
      "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
      "zone": "e3efe6fd-906e-11eb-a875-0242ac150002"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "title": "Nouvelle campagne PAP",
      "brief": "**NOUVEAU**",
      "goal": 200,
      "begin_at": "2022-05-01T00:00:00+02:00",
      "finish_at": "2022-05-31T00:00:00+02:00",
      "survey": {
          "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
      },
      "uuid": "@uuid@",
      "visibility": "local",
      "zone": {
        "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
        "code": "92",
        "name": "Hauts-de-Seine"
      }
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can update a local campaign in a zone I am manager of
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaigns/e3c6e83f-7471-4e8f-b348-6c2eb26723ce?scope=<scope>" with body:
    """
    {
      "title": "**NOUVEAU** Campagne locale du département 92"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "title": "**NOUVEAU** Campagne locale du département 92",
      "brief": null,
      "goal": 100,
      "begin_at": "@string@.isDateTime()",
      "finish_at": "@string@.isDateTime()",
      "uuid": "e3c6e83f-7471-4e8f-b348-6c2eb26723ce",
      "visibility": "local",
      "zone": {
        "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
        "code": "92",
        "name": "Hauts-de-Seine"
      },
      "survey": {
        "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
      }
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can not create a local campaign in a zone I am not manager of
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaigns?scope=<scope>" with body:
      """
      {
        "title": "Nouvelle campagne PAP",
        "brief": "**NOUVEAU**",
        "goal": 200,
        "begin_at": "2022-05-01 00:00:00",
        "finish_at": "2022-05-31 00:00:00",
        "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
        "zone": "e3f1a8e8-906e-11eb-a875-0242ac150002"
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
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can not update a local campaign in a zone I am not manager of
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaigns/74a0d169-1e10-4159-a399-bf499706a2c6?scope=<scope>" with body:
    """
    {
      "title": "**NOUVEAU** Campagne locale de la ville de Nice (06088)"
    }
    """
    Then the response status code should be 403
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario: As a user granted with local scope, I can not create a national campaign
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaigns?scope=referent" with body:
      """
      {
          "title": "Nouvelle campagne PAP",
          "brief": "**NOUVEAU**",
          "goal": 200,
          "begin_at": "2022-05-01 00:00:00",
          "finish_at": "2022-05-31 00:00:00",
          "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
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

  Scenario: As a user granted with local scope, I can not update a national campaign
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9?scope=pap_national_manager" with body:
    """
    {
      "title": "NOUVEAU Campagne de 10 jours suivants",
      "brief": "NOUVEAU **Campagne** de 10 jours suivants",
      "goal": 1000,
      "begin_at": "2022-04-01 00:00:00",
      "finish_at": "2022-04-30 00:00:00",
      "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
    }
    """
    Then the response status code should be 403

  Scenario: As an anonymous user, I can not get the list of campaigns
    Given I send a "GET" request to "/api/v3/pap_campaigns?scope=referent"
    Then the response status code should be 401

  Scenario: As an anonymous user, I can not create a campaign
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaigns?scope=pap_national_manager" with body:
    """
    {
      "title": "Nouvelle campagne PAP",
      "brief": "**NOUVEAU**",
      "goal": 200,
      "begin_at": "2022-05-01 00:00:00",
      "finish_at": "2022-05-31 00:00:00",
      "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
    }
    """
    Then the response status code should be 401

  Scenario: As an anonymous user, I can not update a campaign
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9?scope=pap_national_manager" with body:
    """
    {
      "title": "NOUVEAU Campagne de 10 jours suivants",
      "brief": "NOUVEAU **Campagne** de 10 jours suivants",
      "goal": 1000,
      "begin_at": "2022-04-01 00:00:00",
      "finish_at": "2022-04-30 00:00:00",
      "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
    }
    """
    Then the response status code should be 401

  Scenario: As a logged-in user I can get active PAP campaigns
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    When I send a "GET" request to "/api/v3/pap_campaigns?pagination=false"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
        {
            "title": "Campagne de 10 jours suivants",
            "brief": "**Campagne** de 10 jours suivants",
            "goal": 600,
            "begin_at": "@string@.isDateTime()",
            "finish_at": "@string@.isDateTime()",
            "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
            "visibility": "national",
            "zone": null
        },
        {
            "title": "Campagne de 5 jours suivants",
            "brief": "**Campagne** de 5 jours suivants",
            "goal": 500,
            "begin_at": "@string@.isDateTime()",
            "finish_at": "@string@.isDateTime()",
            "uuid": "1c67b6bd-6da9-4a72-a266-813c419e7024",
            "visibility": "national",
            "zone": null
        }
    ]
    """

  Scenario: As a logged-in user I can get all PAP campaigns
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns?scope=pap_national_manager&page_size=5"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 5,
            "items_per_page": 5,
            "count": 5,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "title": "Campagne de 10 jours suivants",
                "brief": "**Campagne** de 10 jours suivants",
                "goal": 600,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                "nb_surveys": 3,
                "nb_visited_doors": 5,
                "nb_addresses": 4,
                "nb_voters": 7,
                "visibility": "national",
                "zone": null
            },
            {
                "title": "Campagne de 5 jours suivants",
                "brief": "**Campagne** de 5 jours suivants",
                "goal": 500,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "1c67b6bd-6da9-4a72-a266-813c419e7024",
                "nb_surveys": 0,
                "nb_visited_doors": 1,
                "nb_addresses": 4,
                "nb_voters": 7,
                "visibility": "national",
                "zone": null
            },
            {
                "title": "Campagne dans 10 jours",
                "brief": "### Campagne dans 10 jours",
                "goal": 400,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "63460047-c81a-44b9-aec9-152ecf58df93",
                "nb_surveys": 0,
                "nb_visited_doors": 0,
                "nb_addresses": 4,
                "nb_voters": 7,
                "visibility": "national",
                "zone": null
            },
            {
                "title": "Campagne dans 20 jours",
                "brief": "### Campagne dans 20 jours",
                "goal": 400,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "932d67d1-2da6-4695-82f6-42afc20f2e41",
                "nb_surveys": 0,
                "nb_visited_doors": 0,
                "nb_addresses": 4,
                "nb_voters": 7,
                "visibility": "national",
                "zone": null
            },
            {
                "title": "Campagne terminée",
                "brief": null,
                "goal": 100,
                "begin_at": "@string@.isDateTime()",
                "finish_at": "@string@.isDateTime()",
                "uuid": "9ba6b743-5018-4358-bdc0-eb2094010beb",
                "nb_surveys": 0,
                "nb_visited_doors": 1,
                "nb_addresses": 4,
                "nb_voters": 7,
                "visibility": "national",
                "zone": null
            }
        ]
    }
    """

  Scenario: As a logged-in user I can get one PAP campaign
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    When I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "title": "Campagne de 10 jours suivants",
        "brief": "**Campagne** de 10 jours suivants",
        "goal": 600,
        "begin_at": "@string@.isDateTime()",
        "finish_at": "@string@.isDateTime()",
        "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
        "visibility": "national",
        "zone": null
    }
    """

  Scenario: As a logged-in user I can get passed PAP campaign
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns/9ba6b743-5018-4358-bdc0-eb2094010beb?scope=pap_national_manager"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "title": "Campagne terminée",
        "brief": null,
        "goal": 100,
        "begin_at": "@string@.isDateTime()",
        "finish_at": "@string@.isDateTime()",
        "uuid": "9ba6b743-5018-4358-bdc0-eb2094010beb",
        "nb_surveys": 0,
        "nb_visited_doors": 1,
        "nb_addresses": 4,
        "nb_voters": 7,
        "nb_collected_contacts": 0,
        "nb_contact_later": 1,
        "nb_door_open": 0,
        "nb_to_join": 0,
        "average_visit_time": 140,
        "visibility": "national",
        "zone": null
    }
    """

  Scenario: As a logged-in user with no correct rights I cannot get a campaign survey
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile"
    And I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/survey"
    Then the response status code should be 403

  Scenario: As a logged-in user with correct rights I can get a campaign survey
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    And I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/survey"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "uuid": "4c3594d4-fb6f-4e25-ac2e-7ef81694ec47",
        "id": @integer@,
        "type": "national",
        "name": "Les enjeux des 10 prochaines années",
        "questions": [
            {
                "id": @integer@,
                "type": "simple_field",
                "content": "A votre avis quels seront les enjeux des 10 prochaines années?",
                "choices": []
            },
            {
                "id": @integer@,
                "type": "multiple_choice",
                "content": "L'écologie est selon vous, importante pour :",
                "choices": [
                    {
                        "id": @integer@,
                        "content": "L'héritage laissé aux générations futures"
                    },
                    {
                        "id": @integer@,
                        "content": "Le bien-être sanitaire"
                    },
                    {
                        "id": @integer@,
                        "content": "L'aspect financier"
                    },
                    {
                        "id": @integer@,
                        "content": "La préservation de l'environnement"
                    }
                ]
            }
        ]
    }
    """

  Scenario: As a logged-in user with correct rights I can get a campaign survey config
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    And I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/survey-config"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "before_survey": {
            "door_status": [
                {
                    "code": "door_closed",
                    "label": "Porte fermée",
                    "success_status": false
                },
                {
                    "code": "door_open",
                    "label": "Porte ouverte",
                    "success_status": true
                }
            ],
            "response_status": [
                {
                    "code": "dont_accept_to_answer",
                    "label": "Ne souhaite pas répondre",
                    "success_status": false
                },
                {
                    "code": "contact_later",
                    "label": "N'a pas le temps de répondre cette fois-ci",
                    "success_status": false
                },
                {
                    "code": "accept_to_answer",
                    "label": "Accepte d'échanger",
                    "success_status": true
                }
            ]
        },
        "after_survey": [
            {
                "description": "Afin d’améliorer l’analyse des réponses à ce sondage vous pouvez renseigner le profil de votre interlocuteur. Toutes ces informations sont facultatives. ",
                "questions": [
                    {
                        "code": "gender",
                        "type": "choice",
                        "options": {
                            "label": "Quel est votre genre ?",
                            "required": false,
                            "multiple": false,
                            "choices": {
                                "female": "Femme",
                                "male": "Homme"
                            },
                            "widget": "single_row"
                        }
                    },
                    {
                        "code": "age_range",
                        "type": "choice",
                        "options": {
                            "label": "Sa tranche d'âge",
                            "required": false,
                            "multiple": false,
                            "choices": {
                                "less_than_20": "-20 ans",
                                "between_20_24": "20-24 ans",
                                "between_25_39": "25-39 ans",
                                "between_40_54": "40-54 ans",
                                "between_55_64": "55-64 ans",
                                "between_65_80": "65-80 ans",
                                "greater_than_80": "80+ ans"
                            },
                            "widget": "cols_2"
                        }
                    },
                    {
                        "code": "profession",
                        "type": "choice",
                        "options": {
                            "label": "Sa profession",
                            "required": false,
                            "multiple": false,
                            "choices": {
                                "employees": "Employé",
                                "workers": "Ouvrier",
                                "managerial staff": "Cadre",
                                "intermediate_professions": "Profession intermédiaire",
                                "self_contractor": "Indépendant et professions libérales",
                                "retirees": "Retraité",
                                "student": "Étudiant",
                                "unemployed": "En recherche d'emploi"
                            },
                            "widget": "cols_1"
                        }
                    }
                ]
            },
            {
                "description": null,
                "questions": [
                    {
                        "code": "to_contact",
                        "type": "boolean",
                        "options": {
                            "label": "Souhaite-t-il être tenu au courant des résultats de cette consultation et recevoir notre actualité politique par e-mail ?",
                            "required": true,
                            "help": "En cochant oui, vous certifiez qu'il consent à ce que ses données personnelles soient traitées par La République En Marche et qu'il est informé des droits dont il dispose sur ses données - notamment, la possibilité de se désinscrire à tout moment."
                        }
                    },
                    {
                        "code": "profil",
                        "type": "compound",
                        "dependency": {
                            "question": "to_contact",
                            "choices": [
                                true
                            ]
                        },
                        "options": {
                            "label": "Informations personnelles",
                            "required": true,
                            "children": [
                                {
                                    "code": "first_name",
                                    "type": "text",
                                    "options": {
                                        "label": "Prénom",
                                        "required": true,
                                        "placeholder": "Indiquez ici le prénom de la personne rencontrée"
                                    }
                                },
                                {
                                    "code": "last_name",
                                    "type": "text",
                                    "options": {
                                        "label": "Nom",
                                        "required": true,
                                        "placeholder": "Indiquez ici le nom de la personne rencontrée"
                                    }
                                },
                                {
                                    "code": "email_address",
                                    "type": "text",
                                    "options": {
                                        "label": "E-mail",
                                        "required": true,
                                        "placeholder": "Indiquez ici l'e-mail de la personne rencontrée"
                                    }
                                }
                            ]
                        }
                    }
                ]
            }
        ]
    }
    """

  Scenario: As a logged-in user I can get a campaign survey
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    And I send a "GET" request to "/api/v3/pap_campaigns/tutorial"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "content": "**Texte du tutoriel** pour la *campagne* de PAP avec le Markdown"
    }
    """

  Scenario: As a logged-in user I cannot post a pap campaign history with wrong data
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaign_histories" with body:
    """
    {
        "campaign": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
        "status": "invalid"
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "building: Cette valeur ne doit pas être nulle.\nstatus: Le statut n'est pas valide.",
        "violations": [
            {
                "propertyPath": "building",
                "message": "Cette valeur ne doit pas être nulle."
            },
            {
                "propertyPath": "status",
                "message": "Le statut n'est pas valide."
            }
        ]
    }
    """

  Scenario: As a logged-in user I can post a pap campaign history
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaign_histories" with body:
    """
    {
        "begin_at": "2022-01-10 10:10:10",
        "campaign": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
        "building": "0b81ff3d-f895-4e3f-bf6d-ff2a659c1c6f",
        "status": "door_closed",
        "building_block": "A",
        "floor": 1,
        "door": "3"
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "uuid": "@uuid@",
        "status": "door_closed"
    }
    """

  Scenario: As a logged-in user I can update my pap campaign history
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da" with body:
    """
    {
        "status": "accept_to_answer",
        "building": "0b81ff3d-f895-4e3f-bf6d-ff2a659c1c6f",
        "building_block": "C",
        "floor": 2,
        "door": "23",
        "firstName": "Maria",
        "lastName": "Curei",
        "emailAddress": "maria.curie@test.com",
        "gender": "female",
        "ageRange": "between_40_54",
        "profession": "self_contractor",
        "toContact": true,
        "toJoin": false
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "uuid": "6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da",
        "status": "accept_to_answer"
    }
    """

  Scenario: As a logged-in user I cannot update a pap campaign history with invalid data
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da" with body:
    """
    {
        "status": "invalid",
        "email_address": "01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ",
        "gender": "invalid",
        "age_range": "invalid",
        "profession": "invalid"
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "status: Le statut n'est pas valide.\nemail_address: Cette valeur n'est pas une adresse email valide.\nemail_address: L'adresse e-mail est trop longue, 255 caractères maximum.\ngender: Ce sexe n'est pas valide.\nage_range: Cette valeur doit être l'un des choix proposés.\nprofession: Cette valeur doit être l'un des choix proposés.",
        "violations": [
            {
                "propertyPath": "status",
                "message": "Le statut n'est pas valide."
            },
            {
                "propertyPath": "email_address",
                "message": "Cette valeur n'est pas une adresse email valide."
            },
            {
                "propertyPath": "email_address",
                "message": "L'adresse e-mail est trop longue, 255 caractères maximum."
            },
            {
                "propertyPath": "gender",
                "message": "Ce sexe n'est pas valide."
            },
            {
                "propertyPath": "age_range",
                "message": "Cette valeur doit être l'un des choix proposés."
            },
            {
                "propertyPath": "profession",
                "message": "Cette valeur doit être l'un des choix proposés."
            }
        ]
    }
    """

  Scenario: As a logged-in user I can update a pap campaign history
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da" with body:
    """
    {
        "status": "accept_to_answer",
        "building_block": "C",
        "floor": 2,
        "door": "23",
        "firstName": "Maria",
        "lastName": "Curei",
        "emailAddress": "maria.curie@test.com",
        "gender": "female",
        "ageRange": "between_40_54",
        "profession": "self_contractor",
        "voter_status": "registered",
        "voter_postal_code": "92110",
        "toContact": true,
        "toJoin": false
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "uuid": "6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da",
        "status": "accept_to_answer"
    }
    """

  Scenario: As a logged-in user I can not create a campaign with no data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaigns?scope=pap_national_manager" with body:
    """
    {
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "title: Cette valeur ne doit pas être vide.\ngoal: Cette valeur ne doit pas être vide.\nsurvey: Cette valeur ne doit pas être vide.",
        "violations": [
            {
                "propertyPath": "title",
                "message": "Cette valeur ne doit pas être vide."
            },
            {
                "propertyPath": "goal",
                "message": "Cette valeur ne doit pas être vide."
            },
            {
                "propertyPath": "survey",
                "message": "Cette valeur ne doit pas être vide."
            }
        ]
    }
    """

  Scenario: As a logged-in user I can not create a campaign with invalid data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaigns?scope=pap_national_manager" with body:
    """
    {
        "title": "Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP",
        "brief": "**NOUVEAU**",
        "goal": 0,
        "begin_at": "2022-05-01 00:00:00",
        "finish_at": "2022-05-31 00:00:00",
        "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "goal: Cette valeur doit être supérieure à \"0\".",
        "violations": [
            {
                "propertyPath": "goal",
                "message": "Cette valeur doit être supérieure à \"0\"."
            }
        ]
    }
    """

  Scenario: As a logged-in user I can get PAP campaign ranking
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    When I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/ranking"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
        {
            "label": "Individuel",
            "fields": {
                "rank": "Rang",
                "questioner": "Militant",
                "nb_visited_doors": "Portes frappées",
                "nb_surveys": "Questionnaires remplis"
            },
            "items": [
                {
                    "rank": 1,
                    "questioner": "Patrick B.",
                    "nb_visited_doors": 3,
                    "nb_surveys": 3,
                    "current": false
                },
                {
                    "rank": 2,
                    "questioner": "Jacques P.",
                    "nb_visited_doors": 2,
                    "nb_surveys": 0,
                    "current": false
                },
                {
                    "rank": "...",
                    "questioner": "Referent R.",
                    "nb_visited_doors": 0,
                    "nb_surveys": 0,
                    "current": true
                }
            ]
        },
        {
            "label": "Département",
            "fields": {
                "rank": "Rang",
                "department": "Département",
                "nb_visited_doors": "Portes frappées",
                "nb_surveys": "Questionnaires remplis"
            },
            "items": [
                {
                    "rank": 1,
                    "department": "Paris 8ème",
                    "nb_visited_doors": 5,
                    "nb_surveys": 3,
                    "current": false
                },
                {
                    "rank": "...",
                    "department": "Seine-et-Marne",
                    "nb_visited_doors": 0,
                    "nb_surveys": 0,
                    "current": true
                }
            ]
        }
    ]
    """

  Scenario: As a PAP national manager I can get the list of PAP campaign histories
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaign_histories?scope=pap_national_manager&page_size=10"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 7,
            "items_per_page": 10,
            "count": 7,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "questioner": {
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard",
                    "age": @integer@,
                    "gender": "male"
                },
                "campaign": {
                    "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd",
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    }
                },
                "status": "door_open",
                "building_block": "A",
                "floor": 0,
                "door": "01",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 900
            },
            {
                "questioner": {
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard",
                    "age": @integer@,
                    "gender": "male"
                },
                "campaign": {
                    "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd",
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    }
                },
                "status": "dont_accept_to_answer",
                "building_block": "A",
                "floor": 0,
                "door": "02",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 60
            },
            {
                "questioner": {
                    "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                    "first_name": "Patrick",
                    "last_name": "Bialès",
                    "age": @integer@,
                    "gender": "male"
                },
                "campaign": {
                    "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd",
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    }
                },
                "status": "accept_to_answer",
                "building_block": "A",
                "floor": 1,
                "door": "11",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 300
            },
            {
                "questioner": {
                    "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                    "first_name": "Patrick",
                    "last_name": "Bialès",
                    "age": @integer@,
                    "gender": "male"
                },
                "campaign": {
                    "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    },
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd"
                },
                "status": "accept_to_answer",
                "building_block": "A",
                "floor": 1,
                "door": "12",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 420
            },
            {
                "questioner": {
                    "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                    "first_name": "Patrick",
                    "last_name": "Bialès",
                    "age": @integer@,
                    "gender": "male"
                },
                "campaign": {
                    "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    },
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd"
                },
                "status": "accept_to_answer",
                "building_block": "A",
                "floor": 1,
                "door": "13",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 0
            },
            {
                "questioner": {
                    "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
                    "first_name": "Pierre",
                    "last_name": "Kiroule",
                    "age": @integer@,
                    "gender": "male"
                },
                "campaign": {
                    "uuid": "1c67b6bd-6da9-4a72-a266-813c419e7024",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd",
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    }
                },
                "status": "contact_later",
                "building_block": "A",
                "floor": 3,
                "door": "33",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 300
            },
            {
                "questioner": {
                    "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
                    "first_name": "Pierre",
                    "last_name": "Kiroule",
                    "age": @integer@,
                    "gender": "male"
                },
                "campaign": {
                    "uuid": "9ba6b743-5018-4358-bdc0-eb2094010beb",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd",
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    }
                },
                "status": "contact_later",
                "building_block": "A",
                "floor": 0,
                "door": "01",
                "uuid": "@uuid@",
                "created_at": "2021-11-10T10:10:10+01:00",
                "duration": 140
            }
        ]
    }
    """

  Scenario Outline: As a (delegated) referent I can get the list of PAP campaign histories
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaign_histories?scope=<scope>&page_size=10"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 10,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "questioner": {
                    "gender": "male",
                    "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
                    "first_name": "Pierre",
                    "last_name": "Kiroule",
                    "age": @integer@
                },
                "campaign": {
                    "uuid": "e3c6e83f-7471-4e8f-b348-6c2eb26723ce",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "address": {
                        "number": "92",
                        "address": "Boulevard Victor Hugo",
                        "postal_codes": [
                            "92024"
                        ],
                        "city_name": "Clichy",
                        "uuid": "d2c0d38c-2224-41c2-acb5-78b5dad06819"
                    },
                    "uuid": "22f94373-6186-4c6a-a3d5-fd0b8b3d92cf"
                },
                "status": "door_closed",
                "building_block": "A",
                "floor": 0,
                "door": "01",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 0
            }
        ]
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario: As a referent I can get the list of PAP campaign histories
    When I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaign_histories?scope=referent&page_size=10"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 7,
            "items_per_page": 10,
            "count": 7,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "questioner": {
                    "gender": "male",
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard",
                    "age": @integer@
                },
                "campaign": {
                    "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    },
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd"
                },
                "status": "door_open",
                "building_block": "A",
                "floor": 0,
                "door": "01",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 900
            },
            {
                "questioner": {
                    "gender": "male",
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard",
                    "age": @integer@
                },
                "campaign": {
                    "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    },
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd"
                },
                "status": "dont_accept_to_answer",
                "building_block": "A",
                "floor": 0,
                "door": "02",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 60
            },
            {
                "questioner": {
                    "gender": "male",
                    "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                    "first_name": "Patrick",
                    "last_name": "Bialès",
                    "age": @integer@
                },
                "campaign": {
                    "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    },
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd"
                },
                "status": "accept_to_answer",
                "building_block": "A",
                "floor": 1,
                "door": "11",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 300
            },
            {
                "questioner": {
                    "gender": "male",
                    "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                    "first_name": "Patrick",
                    "last_name": "Bialès",
                    "age": @integer@
                },
                "campaign": {
                    "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    },
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd"
                },
                "status": "accept_to_answer",
                "building_block": "A",
                "floor": 1,
                "door": "12",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 420
            },
            {
                "questioner": {
                    "gender": "male",
                    "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                    "first_name": "Patrick",
                    "last_name": "Bialès",
                    "age": @integer@
                },
                "campaign": {
                    "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    },
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd"
                },
                "status": "accept_to_answer",
                "building_block": "A",
                "floor": 1,
                "door": "13",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 0
            },
            {
                "questioner": {
                    "gender": "male",
                    "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
                    "first_name": "Pierre",
                    "last_name": "Kiroule",
                    "age": @integer@
                },
                "campaign": {
                    "uuid": "1c67b6bd-6da9-4a72-a266-813c419e7024",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    },
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd"
                },
                "status": "contact_later",
                "building_block": "A",
                "floor": 3,
                "door": "33",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 300
            },
            {
                "questioner": {
                    "gender": "male",
                    "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
                    "first_name": "Pierre",
                    "last_name": "Kiroule",
                    "age": @integer@
                },
                "campaign": {
                    "uuid": "9ba6b743-5018-4358-bdc0-eb2094010beb",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    },
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd"
                },
                "status": "contact_later",
                "building_block": "A",
                "floor": 0,
                "door": "01",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 140
            }
        ]
    }
    """

  Scenario: As a PAP national manager I can get the list of PAP campaign histories filtered by questioner
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaign_histories?scope=pap_national_manager&questioner=Patrick"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 3,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 2
        },
        "items": [
            {
                "questioner": {
                    "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                    "first_name": "Patrick",
                    "last_name": "Bialès",
                    "age": @integer@,
                    "gender": "male"
                },
                "campaign": {
                    "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd",
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    }
                },
                "status": "accept_to_answer",
                "building_block": "A",
                "floor": 1,
                "door": "11",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 300
            },
            {
                "campaign": {
                    "created_at": "@string@.isDateTime()",
                    "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
                },
                "questioner": {
                    "first_name": "Patrick",
                    "last_name": "Bialès",
                    "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                    "age": @integer@,
                    "gender": "male"
                },
                "building": {
                    "address": {
                        "address": "Rue du Rocher",
                        "city_name": "Paris 8ème",
                        "number": "67",
                        "postal_codes": [
                            "75008"
                        ],
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    },
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd"
                },
                "status": "accept_to_answer",
                "building_block": "A",
                "floor": 1,
                "door": "12",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 420
            }
        ]
    }
    """

  Scenario: As a PAP national manager I can get the list of PAP campaign histories filtered by status
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaign_histories?scope=pap_national_manager&status=contact_later"
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
                "questioner": {
                    "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
                    "first_name": "Pierre",
                    "last_name": "Kiroule",
                    "age": @integer@,
                    "gender": "male"
                },
                "campaign": {
                    "uuid": "1c67b6bd-6da9-4a72-a266-813c419e7024",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd",
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    }
                },
                "status": "contact_later",
                "building_block": "A",
                "floor": 3,
                "door": "33",
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "duration": 300
            },
            {
                "questioner": {
                    "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
                    "first_name": "Pierre",
                    "last_name": "Kiroule",
                    "age": @integer@,
                    "gender": "male"
                },
                "campaign": {
                    "uuid": "9ba6b743-5018-4358-bdc0-eb2094010beb",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd",
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    }
                },
                "status": "contact_later",
                "building_block": "A",
                "floor": 0,
                "door": "01",
                "uuid": "@uuid@",
                "created_at": "2021-11-10T10:10:10+01:00",
                "duration": 140
            }
        ]
    }
    """

  Scenario: As a PAP national manager I can get the list of PAP campaign histories filtered by begin date
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaign_histories?scope=pap_national_manager&createdAt[after]=2021-11-09&createdAt[before]=2021-12-11"
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
                "questioner": {
                    "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
                    "first_name": "Pierre",
                    "last_name": "Kiroule",
                    "age": @integer@,
                    "gender": "male"
                },
                "campaign": {
                    "uuid": "9ba6b743-5018-4358-bdc0-eb2094010beb",
                    "created_at": "@string@.isDateTime()"
                },
                "building": {
                    "uuid": "2bffd913-34fe-48ad-95f4-7381812b93dd",
                    "address": {
                        "number": "67",
                        "address": "Rue du Rocher",
                        "postal_codes": [
                            "75008"
                        ],
                        "city_name": "Paris 8ème",
                        "uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"
                    }
                },
                "status": "contact_later",
                "building_block": "A",
                "floor": 0,
                "door": "01",
                "uuid": "@uuid@",
                "created_at": "2021-11-10T10:10:10+01:00",
                "duration": 140
            }
        ]
    }
    """

  Scenario:  As a PAP national manager I can get the list of a campaign replies
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/replies?scope=pap_national_manager&page_size=10"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 3,
            "items_per_page": 10,
            "count": 3,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "survey": {
                    "uuid": "4c3594d4-fb6f-4e25-ac2e-7ef81694ec47",
                    "name": "Les enjeux des 10 prochaines années",
                    "created_at": "@string@.isDateTime()"
                },
                "pap_campaign_history": {
                    "questioner": {
                        "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                        "first_name": "Patrick",
                        "last_name": "Bialès",
                        "age": @integer@,
                        "gender": "male"
                    },
                    "first_name": null,
                    "last_name": null,
                    "gender": null,
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "begin_at": "@string@.isDateTime()",
                    "finish_at": "@string@.isDateTime()",
                    "duration": 0
                },
                "uuid": "@uuid@",
                "answers": [
                    {
                        "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                        "type": "simple_field",
                        "answer": "Nouvelles technologies"
                    },
                    {
                        "question": "L'écologie est selon vous, importante pour :",
                        "type": "multiple_choice",
                        "answer": [
                            "L'héritage laissé aux générations futures",
                            "Le bien-être sanitaire"
                        ]
                    }
                ]
            },
            {
                "uuid": "@uuid@",
                "survey": {
                    "uuid": "4c3594d4-fb6f-4e25-ac2e-7ef81694ec47",
                    "name": "Les enjeux des 10 prochaines années",
                    "created_at": "@string@.isDateTime()"
                },
                "pap_campaign_history": {
                    "questioner": {
                        "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                        "first_name": "Patrick",
                        "last_name": "Bialès",
                        "age": @integer@,
                        "gender": "male"
                    },
                    "first_name": null,
                    "last_name": null,
                    "gender": null,
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "begin_at": "@string@.isDateTime()",
                    "finish_at": "@string@.isDateTime()",
                    "duration": 420
                },
                "answers": [
                    {
                        "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                        "type": "simple_field",
                        "answer": "Les ressources énergétiques"
                    },
                    {
                        "question": "L'écologie est selon vous, importante pour :",
                        "type": "multiple_choice",
                        "answer": [
                            "L'aspect financier",
                            "La préservation de l'environnement"
                        ]
                    }
                ]
            },
            {
                "uuid": "@uuid@",
                "survey": {
                    "uuid": "4c3594d4-fb6f-4e25-ac2e-7ef81694ec47",
                    "name": "Les enjeux des 10 prochaines années",
                    "created_at": "@string@.isDateTime()"
                },
                "pap_campaign_history": {
                    "questioner": {
                        "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                        "first_name": "Patrick",
                        "last_name": "Bialès",
                        "age": @integer@,
                        "gender": "male"
                    },
                    "first_name": null,
                    "last_name": null,
                    "gender": null,
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "begin_at": "@string@.isDateTime()",
                    "finish_at": "@string@.isDateTime()",
                    "duration": 300
                },
                "answers": [
                    {
                        "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                        "type": "simple_field",
                        "answer": "Vie publique, répartition des pouvoirs et démocratie"
                    },
                    {
                        "question": "L'écologie est selon vous, importante pour :",
                        "type": "multiple_choice",
                        "answer": [
                            "L'héritage laissé aux générations futures",
                            "Le bien-être sanitaire"
                        ]

                    }
                ]
            }
        ]
    }
    """

  Scenario Outline:  As a (delegated) referent I can get the list of a national campaign replies of my managed zones
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/replies?scope=<scope>&page_size=10"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 3,
            "items_per_page": 10,
            "count": 3,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "survey": {
                    "uuid": "4c3594d4-fb6f-4e25-ac2e-7ef81694ec47",
                    "name": "Les enjeux des 10 prochaines années",
                    "created_at": "@string@.isDateTime()"
                },
                "pap_campaign_history": {
                    "questioner": {
                        "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                        "first_name": "Patrick",
                        "last_name": "Bialès",
                        "age": @integer@,
                        "gender": "male"
                    },
                    "first_name": null,
                    "last_name": null,
                    "gender": null,
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "begin_at": "@string@.isDateTime()",
                    "finish_at": "@string@.isDateTime()",
                    "duration": 0
                },
                "uuid": "@uuid@",
                "answers": [
                    {
                        "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                        "type": "simple_field",
                        "answer": "Nouvelles technologies"
                    },
                    {
                        "question": "L'écologie est selon vous, importante pour :",
                        "type": "multiple_choice",
                        "answer": [
                            "L'héritage laissé aux générations futures",
                            "Le bien-être sanitaire"
                        ]
                    }
                ]
            },
            {
                "uuid": "@uuid@",
                "survey": {
                    "uuid": "4c3594d4-fb6f-4e25-ac2e-7ef81694ec47",
                    "name": "Les enjeux des 10 prochaines années",
                    "created_at": "@string@.isDateTime()"
                },
                "pap_campaign_history": {
                    "questioner": {
                        "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                        "first_name": "Patrick",
                        "last_name": "Bialès",
                        "age": @integer@,
                        "gender": "male"
                    },
                    "first_name": null,
                    "last_name": null,
                    "gender": null,
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "begin_at": "@string@.isDateTime()",
                    "finish_at": "@string@.isDateTime()",
                    "duration": 420
                },
                "answers": [
                    {
                        "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                        "type": "simple_field",
                        "answer": "Les ressources énergétiques"
                    },
                    {
                        "question": "L'écologie est selon vous, importante pour :",
                        "type": "multiple_choice",
                        "answer": [
                            "L'aspect financier",
                            "La préservation de l'environnement"
                        ]
                    }
                ]
            },
            {
                "uuid": "@uuid@",
                "survey": {
                    "uuid": "4c3594d4-fb6f-4e25-ac2e-7ef81694ec47",
                    "name": "Les enjeux des 10 prochaines années",
                    "created_at": "@string@.isDateTime()"
                },
                "pap_campaign_history": {
                    "questioner": {
                        "uuid": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                        "first_name": "Patrick",
                        "last_name": "Bialès",
                        "age": @integer@,
                        "gender": "male"
                    },
                    "first_name": null,
                    "last_name": null,
                    "gender": null,
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "begin_at": "@string@.isDateTime()",
                    "finish_at": "@string@.isDateTime()",
                    "duration": 300
                },
                "answers": [
                    {
                        "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                        "type": "simple_field",
                        "answer": "Vie publique, répartition des pouvoirs et démocratie"
                    },
                    {
                        "question": "L'écologie est selon vous, importante pour :",
                        "type": "multiple_choice",
                        "answer": [
                            "L'héritage laissé aux générations futures",
                            "Le bien-être sanitaire"
                        ]

                    }
                ]
            }
        ]
    }
    """
    Examples:
      | user                            | scope                                           |
      | referent-75-77@en-marche-dev.fr | referent                                        |
      | francis.brioul@yahoo.com        | delegated_689757d2-dea5-49d1-95fe-281fc860ff77  |

  Scenario Outline: As a (delegated) referent I get an empty list of a national campaign replies, if no replies in my managed zones
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/replies?scope=<scope>"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 0,
            "items_per_page": 30,
            "count": 0,
            "current_page": 1,
            "last_page": 1
        },
        "items": []
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario: As a PAP national manger I can get PAP campaigns KPI
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns/kpi?scope=pap_national_manager"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "nb_campaigns": "5",
        "nb_ongoing_campaigns": "4",
        "nb_visited_doors": "7",
        "nb_visited_doors_last_30d": "6",
        "nb_surveys": "3",
        "nb_surveys_last_30d": "3"
    }
    """

  Scenario Outline: As a (delegated) referent I can get PAP campaigns KPI
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns/kpi?scope=<scope>"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "nb_campaigns": "7",
        "nb_ongoing_campaigns": "6",
        "nb_visited_doors": "9",
        "nb_visited_doors_last_30d": "8",
        "nb_surveys": "3",
        "nb_surveys_last_30d": "3"
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario: As a PAP national manager I can get a PAP questioners with their stats
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/questioners?scope=pap_national_manager"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 2,
            "items_per_page": 100,
            "count": 2,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "first_name": "Patrick",
                "last_name": "Bialès",
                "nb_visited_doors": "3",
                "nb_surveys": "3",
                "nb_accept_to_answer": "3",
                "nb_dont_accept_to_answer": "0",
                "nb_contact_later": "0",
                "nb_door_open": "0",
                "door_closed": "0"
            },
            {
                "first_name": "Jacques",
                "last_name": "Picard",
                "nb_visited_doors": "2",
                "nb_surveys": "0",
                "nb_accept_to_answer": "0",
                "nb_dont_accept_to_answer": "1",
                "nb_contact_later": "0",
                "nb_door_open": "1",
                "door_closed": "0"
            }
        ]
    }
    """
    When I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/questioners?scope=pap_national_manager&page_size=1&page=2"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 2,
            "items_per_page": 1,
            "count": 1,
            "current_page": 2,
            "last_page": 2
        },
        "items": [
            {
                "first_name": "Jacques",
                "last_name": "Picard",
                "nb_visited_doors": "2",
                "nb_surveys": "0",
                "nb_accept_to_answer": "0",
                "nb_dont_accept_to_answer": "1",
                "nb_contact_later": "0",
                "nb_door_open": "1",
                "door_closed": "0"
            }
        ]
    }
    """

  Scenario: As a referent I can get a PAP questioners with their stats
    Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/questioners?scope=referent"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 100,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "first_name": "Jacques",
                "last_name": "Picard",
                "nb_visited_doors": "2",
                "nb_surveys": "0",
                "nb_accept_to_answer": "0",
                "nb_dont_accept_to_answer": "1",
                "nb_contact_later": "0",
                "nb_door_open": "1",
                "door_closed": "0"
            }
        ]
    }
    """

  Scenario Outline: As a (delegated) referent I get an empty list of PAP questioners, if no replies in my managed zones
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/questioners?scope=<scope>"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 0,
            "items_per_page": 100,
            "count": 0,
            "current_page": 1,
            "last_page": 1
        },
        "items": []
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
