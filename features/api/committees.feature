@api
Feature:
    In order to manage committees
    As a logged-in user
    I should be able to list, create and edit committees

    Scenario: As referent I cannot get my committees without scope parameter
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/committees"
        Then the response status code should be 403

    Scenario: As referent I cannot get committees outside my zone
        Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/committees?scope=referent"
        Then the JSON nodes should be equal to:
            | metadata.count | 0  |

    Scenario Outline: As a user granted with local scope, I can get committees in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/committees?scope=<scope>"
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
                    "description": "Un petit comité avec seulement 3 communes",
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "name": "Comité des 3 communes"
                },
                {
                    "description": "Un petit comité avec seulement 3 communes",
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "name": "Second Comité des 3 communes"
                }
            ]
        }
        """
        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | referent                                       |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a user granted with local scope, I can get geo zone available for a new committee
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/zone/autocomplete?scope=referent&q=Hauts&availableForCommittee=true"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        [
            {
                "uuid": "e3f17cac-906e-11eb-a875-0242ac150002",
                "type": "city_community",
                "postal_code": [],
                "code": "200040954",
                "name": "CC des Hauts de Flandre"
            }
        ]
        """

    Scenario Outline: I can create a committee with some zones
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/zone/autocomplete?scope=<scope>&q=Fontenay-aux-Roses&types[]=city&types[]=canton&availableForCommittee=true"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        [
            {
                "uuid": "e3f2c5ec-906e-11eb-a875-0242ac150002",
                "type": "city",
                "postal_code": ["92260"],
                "code": "92032",
                "name": "Fontenay-aux-Roses"
            }
        ]
        """
        When I send a "POST" request to "/api/v3/committees?scope=<scope>" with body:
        """
        {
            "name": "test 1",
            "description": "my desc",
            "zones": [
                "e3f154b1-906e-11eb-a875-0242ac150002",
                "e3f2c5ec-906e-11eb-a875-0242ac150002",
                "e3f2cb17-906e-11eb-a875-0242ac150002"
            ]
        }
        """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
            "name": "test 1",
            "description": "my desc",
            "uuid": "@uuid@",
            "created_at": "@string@.isDateTime()",
            "updated_at": "@string@.isDateTime()"
        }
        """
        When I send a "GET" request to "/api/v3/zone/autocomplete?scope=<scope>&q=Fontenay-aux-Roses&types[]=city&availableForCommittee=true"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        []
        """
        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | referent                                       |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: I cannot create a committee with invalid zone type
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/committees?scope=<scope>" with body:
        """
        {
            "name": "test 1",
            "description": "my desc",
            "zones": [
                "e3f0ebd6-906e-11eb-a875-0242ac150002"
            ]
        }
        """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
            "type": "https://tools.ietf.org/html/rfc2616#section-10",
            "title": "An error occurred",
            "detail": "zones: Le type de la zone est invalide",
            "violations": [
                {
                    "propertyPath": "zones",
                    "message": "Le type de la zone est invalide",
                    "code": null
                }
            ]
        }
        """
        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | referent                                       |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can get geo zone available for a new committee
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/committees/used-zones?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        [
            {
                "type": "city",
                "code": "92002"
            },
            {
                "type": "city",
                "code": "92004"
            },
            {
                "type": "city",
                "code": "92007"
            },
            {
                "type": "city",
                "code": "92012"
            },
            {
                "type": "city",
                "code": "92014"
            },
            {
                "type": "city",
                "code": "92019"
            }
        ]
        """
        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | referent                                       |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can get a committee election
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/committee_elections/278fcb58-53b4-4798-a3be-e5bb92f7f0f2?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
            "committee": {
                "uuid": "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3"
            },
            "designation": {
                "custom_title": "Election AL - second comité des 3 communes",
                "election_creation_date": "@string@.isDateTime()",
                "vote_start_date": "@string@.isDateTime()",
                "vote_end_date": "@string@.isDateTime()",
                "uuid": "6c7ca0c7-d656-47c3-a345-170fb43ffd1a"
            },
            "candidacies_groups": [
                {
                    "uuid": "5d88db4a-9f3e-470e-8cc6-145dc6c7517a",
                    "candidacies": [
                        {
                            "committee_membership": {
                                "adherent": {
                                    "gender": "female",
                                    "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                                    "first_name": "Gisele",
                                    "last_name": "Berthoux"
                                },
                                "uuid": "@uuid@"
                            },
                            "uuid": "@uuid@"
                        }
                    ]
                },
                {
                    "uuid": "7f048f8e-0096-4cd2-b348-f19579223d6f",
                    "candidacies": []
                }
            ],
            "uuid": "278fcb58-53b4-4798-a3be-e5bb92f7f0f2"
        }
        """
        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | referent                                       |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I cannot create a candidacies group on a past or started committee election
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/committee_candidacies_groups?scope=<scope>" with body:
        """
        {
          "election": "f86ee969-5eca-4666-bcd4-7f7388372e0b"
        }
        """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON node "detail" should be equal to "Vous ne pouvez pas créer de liste sur une élection en cours"
        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | referent                                       |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can create a candidacies group on committee election
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/committee_candidacies_groups?scope=<scope>" with body:
        """
        {
          "election": "278fcb58-53b4-4798-a3be-e5bb92f7f0f2"
        }
        """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
            "uuid": "@uuid@",
            "election": {
                "uuid": "278fcb58-53b4-4798-a3be-e5bb92f7f0f2"
            },
            "candidacies": []
        }
        """
        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | referent                                       |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a grand user with local scope, I cannot delete a non empty list
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "DELETE" request to "/api/v3/committee_candidacies_groups/5d88db4a-9f3e-470e-8cc6-145dc6c7517a?scope=<scope>"
        And the response status code should be 403
        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | referent                                       |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a grand user with local scope, I can delete an empty list
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "DELETE" request to "/api/v3/committee_candidacies_groups/7f048f8e-0096-4cd2-b348-f19579223d6f?scope=<scope>"
        And the response status code should be 204
        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | referent                                       |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
