@api
@renaissance
Feature:
    In order to see teams
    As a logged-in user
    I should be able to access API teams

    Scenario Outline: As a logged-in user without phoning team manager right I can not access teams endpoints
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Web"
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
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
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
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherents/autocomplete?q=petit&scope=phoning_national_manager"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "registered_at": "@string@.isDateTime()",
                    "uuid": "@uuid@",
                    "id": "@string@-@string@",
                    "first_name": "Adrien",
                    "last_name": "Petit",
                    "postal_code": "77000",
                    "email_address": "adherent-male-a@en-marche-dev.fr"
                },
                {
                    "registered_at": "@string@.isDateTime()",
                    "uuid": "@uuid@",
                    "id": "@string@-@string@",
                    "first_name": "Agathe",
                    "last_name": "Petit",
                    "postal_code": "77000",
                    "email_address": "adherent-female-a@en-marche-dev.fr"
                },
                {
                    "registered_at": "@string@.isDateTime()",
                    "uuid": "@uuid@",
                    "id": "@string@-@string@",
                    "first_name": "Étienne",
                    "last_name": "Petit",
                    "postal_code": "77000",
                    "email_address": "adherent-male-b@en-marche-dev.fr"
                }
            ]
            """
        When I send a "GET" request to "/api/v3/adherents/autocomplete?q=lolodie.dutemps@hotnix.tld&scope=phoning_national_manager"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "registered_at": "@string@.isDateTime()",
                    "uuid": "@uuid@",
                    "id": "@string@-@string@",
                    "first_name": "Élodie",
                    "last_name": "Dutemps",
                    "postal_code": "368645",
                    "email_address": "lolodie.dutemps@hotnix.tld"
                }
            ]
            """

    Scenario Outline: As a (delegated) referent I can search an adherent with autocomplete search
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherents/autocomplete?q=bert&scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "first_name": "Gisele",
                    "last_name": "Berthoux",
                    "id": "@string@-@string@",
                    "postal_code": "92110",
                    "registered_at": "2017-01-08T05:55:43+01:00",
                    "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                    "email_address": "gisele-berthoux@caramail.com"
                }
            ]
            """
        When I send a "GET" request to "/api/v3/adherents/autocomplete?q=jacques&scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "first_name": "Jacques",
                    "last_name": "Picard",
                    "id": "@string@-@string@",
                    "postal_code": "75008",
                    "registered_at": "@string@.isDateTime()",
                    "uuid": "@uuid@",
                    "email_address": "jacques.picard@en-marche.fr"
                }
            ]
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As an anonymous I can not remove an adherent from a team
        When I send a "DELETE" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/members/918f07e5-676b-49c0-b76d-72ce01cb2404?scope=phoning_national_manager"
        Then the response status code should be 401

    Scenario: As a logged-in user with phoning team manager right I can not remove an adherent who does not exist from a team
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "DELETE" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/members/918f07e5-676b-49c0-b76d-17816bf2d819?scope=phoning_national_manager"
        Then the response status code should be 404

    Scenario: As a user granted with national scope, I can get the list of national teams only
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/teams?scope=phoning_national_manager"
        Then the response status code should be 200
        And the response should be in JSON
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
                        "name": "Équipe à supprimer",
                        "uuid": "389a40c3-d8c1-4611-bf52-f172088066db",
                        "visibility": "national",
                        "is_deletable": true,
                        "zone": null,
                        "members_count": 1,
                        "creator": "Admin"
                    },
                    {
                        "name": "Deuxième équipe de phoning",
                        "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                        "visibility": "national",
                        "is_deletable": false,
                        "zone": null,
                        "members_count": 4,
                        "creator": "Admin"
                    }
                ]
            }
            """

    Scenario: As a user granted with national scope, I can create a national team
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/teams?scope=phoning_national_manager" with body:
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
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=phoning_national_manager" with body:
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

    Scenario: As a connected user, I can delete a team
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "DELETE" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=phoning_national_manager"
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "title": "An error occurred",
                "detail": "Vous ne pouvez pas supprimer ce groupe car il est utilisé"
            }
            """
        When I send a "DELETE" request to "/api/v3/teams/389a40c3-d8c1-4611-bf52-f172088066db?scope=phoning_national_manager"
        Then the response status code should be 200

    Scenario: As a user granted with national scope, I can not create a local team
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/teams?scope=phoning_national_manager" with body:
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
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "zone",
                        "message": "Un rôle national ne peut pas définir de zone."
                    }
                ]
            }
            """

    Scenario: As a user granted with national scope, I can not update a local team
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/teams/ba9ab5dd-c8da-4721-8acb-5a96e285aec3?scope=phoning_national_manager" with body:
            """
            {
                "name": "Equipe d'appel - IDF"
            }
            """
        Then the response status code should be 403

    Scenario Outline: As a user granted with local scope, I can get the list of local teams in the zones I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/teams?scope=<scope>"
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
                        "is_deletable": true,
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
                        "is_deletable": true,
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

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can get a local team in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/teams/ba9ab5dd-c8da-4721-8acb-5a96e285aec3?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "name": "Équipe locale de la ville de Lille (59350)",
                "uuid": "ba9ab5dd-c8da-4721-8acb-5a96e285aec3",
                "visibility": "local",
                "zone": {
                    "code": "59350",
                    "name": "Lille",
                    "uuid": "e3f21338-906e-11eb-a875-0242ac150002"
                },
                "members": [
                    {
                        "adherent_uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                        "first_name": "Lucie",
                        "last_name": "Olivera",
                        "postal_code": "75009",
                        "registered_at": "@string@.isDateTime()"
                    }
                ],
                "creator": "Admin"
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can create a local team in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/teams?scope=<scope>" with body:
            """
            {
                "name": "Nouvelle équipe locale de phoning dans le 92 <title>",
                "zone": "e3efe6fd-906e-11eb-a875-0242ac150002"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "name": "Nouvelle équipe locale de phoning dans le 92 <title>",
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

        Examples:
            | user                      | scope                                          | title |
            | referent@en-marche-dev.fr | president_departmental_assembly                | ref   |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 | sen   |

    Scenario Outline: As a user granted with local scope, I can update a local team in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/teams/ba9ab5dd-c8da-4721-8acb-5a96e285aec3?scope=<scope>" with body:
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

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a user granted with local scope, I can not get a local team in a zone I am not manager of
        Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/teams/a4ad9bde-9fd5-4eda-92e5-9e5576cac9e2?scope=president_departmental_assembly"
        Then the response status code should be 403

    Scenario: As a user granted with local scope, I can not create a local team in a zone I am not manager of
        Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/teams?scope=president_departmental_assembly" with body:
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
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "zone",
                        "message": "La zone spécifiée n'est pas gérée par votre rôle."
                    }
                ]
            }
            """

    Scenario: As a user granted with local scope, I can not update a local team in a zone I am not manager of
        Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/teams/ba9ab5dd-c8da-4721-8acb-5a96e285aec3?scope=president_departmental_assembly" with body:
            """
            {
                "name": "Equipe d'appel - 59"
            }
            """
        Then the response status code should be 403

    Scenario Outline: As a user granted with local scope, I can not create a national team
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/teams?scope=<scope>" with body:
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
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "zone",
                        "message": "Veuillez spécifier une zone."
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can not update a national team
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146?scope=<scope>" with body:
            """
            {
                "name": "Equipe d'appel - IDF"
            }
            """
        Then the response status code should be 403

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As an anonymous user, I can not get the list of teams
        Given I send a "GET" request to "/api/v3/teams?scope=president_departmental_assembly"
        Then the response status code should be 401

    Scenario: As an anonymous user, I can not create a team
        Given I send a "POST" request to "/api/v3/teams?scope=president_departmental_assembly" with body:
            """
            {
                "name": "Nouvelle équipe locale de phoning dans le 92",
                "zone": "e3efe6fd-906e-11eb-a875-0242ac150002"
            }
            """
        Then the response status code should be 401

    Scenario: As an anonymous user, I can not update a team
        Given I send a "PUT" request to "/api/v3/teams/ba9ab5dd-c8da-4721-8acb-5a96e285aec3?scope=president_departmental_assembly" with body:
            """
            {
                "name": "Equipe d'appel - 59"
            }
            """
        Then the response status code should be 401

    Scenario: As a user granted with national scope, I cannot create a national team with the same name
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/teams?scope=phoning_national_manager" with body:
            """
            {
                "name": "Première équipe de phoning"
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
                        "message": "Une équipe porte déjà le même nom."
                    }
                ]
            }
            """

        When I send a "POST" request to "/api/v3/teams?scope=phoning_national_manager" with body:
            """
            {
                "name": "Équipe locale du département 92"
            }
            """
        Then the response status code should be 201

    Scenario Outline: As a user granted with local scope, I cannot create a team with the same name and zone
        When I am logged with "<user>" via OAuth client "JeMengage Web"
        And I send a "POST" request to "/api/v3/teams?scope=<scope>" with body:
            """
            {
                "name": "Équipe locale du département 92",
                "zone": "e3efe6fd-906e-11eb-a875-0242ac150002"
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
                        "message": "Une équipe porte déjà le même nom."
                    }
                ]
            }
            """

        When I send a "POST" request to "/api/v3/teams?scope=<scope>" with body:
            """
            {
                "name": "Première équipe de phoning",
                "zone": "e3efe6fd-906e-11eb-a875-0242ac150002"
            }
            """
        Then the response status code should be 201

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a user granted with team feature, I can add a member to a team
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
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
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
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
        When I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/add-members?scope=phoning_national_manager" with body:
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
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        # Empty request
        When I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/add-members?scope=phoning_national_manager"
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            "Vous devez fournir l'id d'au moins un membre."
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
                "status": "error",
                "message": "Validation Failed",
                "violations": [
                    {
                        "propertyPath": "[0].adherent_uuid",
                        "message": "Cette valeur ne doit pas être vide."
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
                "status": "error",
                "message": "Validation Failed",
                "violations": [
                    {
                        "propertyPath": "[0].adherent_uuid",
                        "message": "Cette valeur ne doit pas être vide."
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
                "status": "error",
                "message": "Validation Failed",
                "violations": [
                    {
                        "propertyPath": "[0].adherent_uuid",
                        "message": "Aucun adhérent trouvé pour l'UUID \"c1051bb4-d103-4f74-8988-acbcafc7fdc3\"."
                    }
                ]
            }
            """

    Scenario: As an anonymous user, I can not add a member to a team
        When I send a "PUT" request to "/api/v3/teams/6434f2ac-edd0-412a-9c4b-99ab4b039146/add-members?scope=phoning_national_manager" with body:
            """
            [
                {
                    "adherent_uuid": "acc73b03-9743-47d8-99db-5a6c6f55ad67"
                }
            ]
            """
        Then the response status code should be 401

    Scenario: As a user granted with team feature, I can remove a member from a team
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
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
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
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
                        "is_deletable": false,
                        "members_count": 4,
                        "creator": "Admin"
                    }
                ]
            }
            """

    Scenario: As a logged-in animator I can search an adherent with autocomplete search
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherents/autocomplete?q=adherent%2056%20&scope=president_departmental_assembly"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "registered_at": "@string@.isDateTime()",
                    "uuid": "@uuid@",
                    "id": "@string@-@string@",
                    "first_name": "Adherent 56",
                    "last_name": "Fa56ke",
                    "postal_code": "77000",
                    "email_address": "adherent-female-56@en-marche-dev.fr"
                }
            ]
            """
        When I send a "GET" request to "/api/v3/adherents/autocomplete?q=123-789%20&scope=president_departmental_assembly"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "registered_at": "@string@.isDateTime()",
                    "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                    "id": "123-789",
                    "first_name": "Carl",
                    "last_name": "Mirabeau",
                    "postal_code": "77190",
                    "email_address": "carl999@example.fr"
                }
            ]
            """
