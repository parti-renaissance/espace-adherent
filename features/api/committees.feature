@api
@renaissance
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
        When I send a "GET" request to "/api/v3/committees?scope=president_departmental_assembly"
        Then the JSON nodes should be equal to:
            | metadata.count | 2 |

    Scenario: As a user granted with local scope, I can get committees in a zone I am manager of
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/committees?scope=president_departmental_assembly"
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
                        "name": "Comité des 3 communes",
                        "members_count": 10,
                        "adherents_count": 10,
                        "members_em_count": 0,
                        "sympathizers_count": 0,
                        "animator": {
                            "uuid": "@uuid@",
                            "id": "@string@",
                            "first_name": "Adherent 55",
                            "last_name": "Fa55ke",
                            "image_url": null,
                            "role": "Responsable comité local"
                        }
                    },
                    {
                        "description": "Un petit comité avec seulement 3 communes",
                        "uuid": "@uuid@",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()",
                        "name": "Second Comité des 3 communes",
                        "members_count": 3,
                        "adherents_count": 2,
                        "members_em_count": 0,
                        "sympathizers_count": 0,
                        "animator": {
                            "uuid": "@uuid@",
                            "id": "@string@",
                            "first_name": "Adherent 56",
                            "last_name": "Fa56ke",
                            "image_url": null,
                            "role": "Responsable comité local"
                        }
                    }
                ]
            }
            """
        When I send a "GET" request to "/api/v3/committees/8c4b48ec-9290-47ae-a5db-d1cf2723e8b3?scope=president_departmental_assembly"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "members_count": 3,
                "adherents_count": 2,
                "members_em_count": 0,
                "sympathizers_count": 0,
                "description": "Un petit comité avec seulement 3 communes",
                "zones": [
                    {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92012",
                        "name": "Boulogne-Billancourt",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92014",
                        "name": "Bourg-la-Reine",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92024",
                        "name": "Clichy",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    }
                ],
                "animator": {
                    "uuid": "@uuid@",
                    "id": "@string@",
                    "first_name": "Adherent 56",
                    "last_name": "Fa56ke",
                    "image_url": null,
                    "role": "Responsable comité local"
                },
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "name": "Second Comité des 3 communes",
                "committee_election": {
                    "uuid": "@uuid@",
                    "status": "not_started"
                }
            }
            """

    Scenario Outline: As a user granted with local scope, I can update a committee
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/committees/8c4b48ec-9290-47ae-a5db-d1cf2723e8b3?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "members_count": 3,
                "adherents_count": 2,
                "members_em_count": 0,
                "sympathizers_count": 0,
                "description": "Un petit comité avec seulement 3 communes",
                "zones": [
                    {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92012",
                        "name": "Boulogne-Billancourt",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92014",
                        "name": "Bourg-la-Reine",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92024",
                        "name": "Clichy",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    }
                ],
                "animator": {
                    "uuid": "@uuid@",
                    "id": "@string@",
                    "first_name": "Adherent 56",
                    "last_name": "Fa56ke",
                    "image_url": null,
                    "role": "Responsable comité local"
                },
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "name": "Second Comité des 3 communes",
                "committee_election": {
                    "uuid": "@uuid@",
                    "status": "not_started"
                }
            }
            """
        When I send a "PUT" request to "/api/v3/committees/8c4b48ec-9290-47ae-a5db-d1cf2723e8b3?scope=<scope>" with body:
            """
            {
                "name": "test 1",
                "description": "my desc"
            }
            """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "members_count": 3,
                "adherents_count": 2,
                "members_em_count": 0,
                "sympathizers_count": 0,
                "description": "my desc",
                "zones": [
                    {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92012",
                        "name": "Boulogne-Billancourt",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92014",
                        "name": "Bourg-la-Reine",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92024",
                        "name": "Clichy",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    }
                ],
                "animator": {
                    "uuid": "@uuid@",
                    "id": "@string@",
                    "first_name": "Adherent 56",
                    "last_name": "Fa56ke",
                    "image_url": null,
                    "role": "Responsable comité local"
                },
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "name": "test 1",
                "committee_election": {
                    "uuid": "@uuid@",
                    "status": "not_started"
                }
            }
            """
        When I send a "PUT" request to "/api/v3/committees/8c4b48ec-9290-47ae-a5db-d1cf2723e8b3/animator?scope=<scope>" with body:
            """
            {
                "animator": "2f69db3c-ecd7-4a8a-bd23-bb4c9cfd70cf"
            }
            """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "members_count": 3,
                "adherents_count": 2,
                "members_em_count": 0,
                "sympathizers_count": 0,
                "description": "my desc",
                "zones": [
                    {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92012",
                        "name": "Boulogne-Billancourt",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92014",
                        "name": "Bourg-la-Reine",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    },
                    {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92024",
                        "name": "Clichy",
                        "created_at": "@string@.isDateTime()",
                        "updated_at": "@string@.isDateTime()"
                    }
                ],
                "animator": {
                    "uuid": "@uuid@",
                    "id": "@string@",
                    "first_name": "Referent75and77",
                    "last_name": "Referent75and77",
                    "image_url": null,
                    "role": "Responsable comité local"
                },
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "name": "test 1",
                "committee_election": {
                    "uuid": "@uuid@",
                    "status": "not_started"
                }
            }
            """

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a user granted with local scope, I can get geo zone available for a new committee
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/zone/autocomplete?scope=president_departmental_assembly&q=Hauts&availableForCommittee=true"
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
                "updated_at": "@string@.isDateTime()",
                "members_count": 0,
                "adherents_count": 0,
                "members_em_count": 0,
                "sympathizers_count": 0,
                "animator": null
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
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: I cannot create a committee with invalid zone type
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/committees?scope=<scope>" with body:
            """
            {
                "name": "test 1",
                "description": "my desc",
                "zones": ["e3f0ebd6-906e-11eb-a875-0242ac150002"]
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
                        "propertyPath": "zones",
                        "message": "Le type de la zone est invalide"
                    }
                ]
            }
            """

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can get geo zone available for a new committee
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/committees/used-zones?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                { "type": "borough", "code": "13203" },
                { "type": "city", "code": "92002" },
                { "type": "city", "code": "92004" },
                { "type": "city", "code": "92007" },
                { "type": "city", "code": "92012" },
                { "type": "city", "code": "92014" },
                { "type": "city", "code": "92024" },
                { "type": "city", "code": "77152" },
                { "type": "city", "code": "77186" },
                { "type": "city", "code": "76540" }
            ]
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

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
                "uuid": "278fcb58-53b4-4798-a3be-e5bb92f7f0f2",
                "status": "not_started",
                "votes_count": null,
                "voters_count": null
            }
            """

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
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
        And the JSON node "violations[0].message" should be equal to "Vous ne pouvez pas créer de liste sur une élection en cours"

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
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
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a grand user with local scope, I cannot delete a non empty list
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "DELETE" request to "/api/v3/committee_candidacies_groups/5d88db4a-9f3e-470e-8cc6-145dc6c7517a?scope=<scope>"
        And the response status code should be 403

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a grand user with local scope, I can delete an empty list
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "DELETE" request to "/api/v3/committee_candidacies_groups/7f048f8e-0096-4cd2-b348-f19579223d6f?scope=<scope>"
        And the response status code should be 204

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can get a candidate information
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/committee_candidacies/50dd9672-69ca-46e1-9353-c2e0d6c03333?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
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
                "candidacies_group": {
                    "uuid": "5d88db4a-9f3e-470e-8cc6-145dc6c7517a"
                },
                "uuid": "50dd9672-69ca-46e1-9353-c2e0d6c03333"
            }
            """

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can add a candidate to a group
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/committee_candidacies?scope=<scope>" with body:
            """
            {
                "adherent": "15b63931-cb1a-46c6-8801-ca32366f8ee3",
                "candidacies_group": "7f048f8e-0096-4cd2-b348-f19579223d6f"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "committee_membership": {
                    "adherent": {
                        "gender": "male",
                        "uuid": "15b63931-cb1a-46c6-8801-ca32366f8ee3",
                        "first_name": "Louis",
                        "last_name": "Roche"
                    },
                    "uuid": "@uuid@"
                },
                "candidacies_group": {
                    "uuid": "7f048f8e-0096-4cd2-b348-f19579223d6f"
                },
                "uuid": "@uuid@"
            }
            """

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can delete a candidate
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "DELETE" request to "/api/v3/committee_candidacies/50dd9672-69ca-46e1-9353-c2e0d6c03333?scope=<scope>"
        And the response status code should be 204

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I cannot add a candidate to a past or ongoing committee election
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/committee_candidacies?scope=<scope>" with body:
            """
            {
                "adherent": "b4219d47-3138-5efd-9762-2ef9f9495084",
                "candidacies_group": "f8a426f3-8014-4803-95b5-8077300755c6"
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON node "violations[0].message" should be equal to "Vous ne pouvez pas créer de candidature sur une élection en cours"

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a grand user with local scope, I cannot delete a candidate on a past or ongoing committee election
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "DELETE" request to "/api/v3/committee_candidacies/d229453d-a9dc-4392-a320-d9536c93b5fe?scope=<scope>"
        And the response status code should be 403

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a grand user with local scope, I cannot create a candidate whose adherent account is not in my managed area
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/committee_candidacies?scope=<scope>" with body:
            """
            {
                "adherent": "25e75e2f-2f73-4f51-8542-bd511ba6a945",
                "candidacies_group": "7f048f8e-0096-4cd2-b348-f19579223d6f"
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON node "violations[0].message" should be equal to "L'adhérent ne fait pas partie de votre zone de couverture."

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a grand user with local scope, I can delete a committee
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "DELETE" request to "/api/v3/committees/5e00c264-1d4b-43b8-862e-29edc38389b3?scope=<scope>"
        Then the response status code should be 204
        And the message "RefreshCommitteeMembershipsInZoneCommand" should be dispatched

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
