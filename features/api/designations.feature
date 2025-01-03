@api
@renaissance
Feature:
    In order to manage designations
    As a logged-in user
    I should be able to access designations API

    Scenario Outline: As a user granted with local scope, I cannot create a designation with invalid payload
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "POST" request to "/api/v3/designations?scope=<scope>" with body:
            """
            {}
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
                        "propertyPath": "custom_title",
                        "message": "Cette valeur ne doit pas être vide."
                    },
                    {
                        "propertyPath": "type",
                        "message": "Cette valeur ne doit pas être vide."
                    },
                    {
                        "propertyPath": "vote_end_date",
                        "message": "La date de clôture doit être postérieur à la date de début"
                    },
                    {
                        "propertyPath": "description",
                        "message": "Cette valeur ne doit pas être vide."
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I cannot create a committee designation without entity uuid
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "POST" request to "/api/v3/designations?scope=<scope>" with body:
            """
            {
                "custom_title": "Élection de comité local",
                "type": "committee_supervisor",
                "vote_start_date": "+12 days",
                "vote_end_date": "+17 days",
                "description": "lorem ipsum..."
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON node "violations[0].message" should be equal to "Un identifiant est requis pour ce champs."

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can create a designation
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "POST" request to "/api/v3/designations?scope=<scope>" with body:
            """
            {
                "custom_title": "Élection de comité local",
                "type": "committee_supervisor",
                "vote_start_date": "+12 days",
                "vote_end_date": "+17 days",
                "description": "lorem ipsum...",
                "election_entity_identifier": "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "custom_title": "Élection de comité local",
                "type": "committee_supervisor",
                "election_creation_date": "@string@.isDateTime()",
                "vote_start_date": "@string@.isDateTime()",
                "vote_end_date": "@string@.isDateTime()",
                "description": "lorem ipsum...",
                "target_year": null,
                "fully_editable": true,
                "created_at": "@string@.isDateTime()",
                "questions": [],
                "election_entity_identifier": "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3",
                "uuid": "@uuid@",
                "is_canceled": false
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can get a designation
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/designations/7fb0693e-1dad-44c6-984b-19e99603ea2c?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "custom_title": "Election AL - comité des 3 communes",
                "type": "committee_supervisor",
                "election_creation_date": "@string@.isDateTime()",
                "vote_start_date": "@string@.isDateTime()",
                "vote_end_date": "@string@.isDateTime()",
                "target_year": null,
                "fully_editable": false,
                "questions": [],
                "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit",
                "election_entity_identifier": "5e00c264-1d4b-43b8-862e-29edc38389b3",
                "uuid": "7fb0693e-1dad-44c6-984b-19e99603ea2c",
                "is_canceled": false,
                "created_at": "@string@.isDateTime()"
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can update schedule a designation
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "PUT" request to "/api/v3/designations/6c7ca0c7-d656-47c3-a345-170fb43ffd1a?scope=<scope>" with body:
            """
            {
                "custom_title": "mise à jour de l'Élection de comité local",
                "type": "committee_supervisor",
                "vote_start_date": "+12 days",
                "vote_end_date": "+17 days",
                "description": "lorem ipsum...",
                "election_entity_identifier": "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3"
            }
            """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "custom_title": "mise à jour de l'Élection de comité local",
                "type": "committee_supervisor",
                "election_creation_date": "@string@.isDateTime()",
                "vote_start_date": "@string@.isDateTime()",
                "vote_end_date": "@string@.isDateTime()",
                "target_year": null,
                "fully_editable": true,
                "questions": [],
                "description": "lorem ipsum...",
                "election_entity_identifier": "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3",
                "uuid": "6c7ca0c7-d656-47c3-a345-170fb43ffd1a",
                "is_canceled": false,
                "created_at": "@string@.isDateTime()"
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can view election results
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/designations/95da3939-f178-4d67-9237-61d4ea57e33c/results?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "candidate_group_results": [
                        {
                            "candidate_group": {
                                "elected": false,
                                "candidates": [
                                    {
                                        "first_name": "Adherent 50",
                                        "last_name": "Fa50ke",
                                        "gender": "female"
                                    },
                                    {
                                        "first_name": "Adherent 32",
                                        "last_name": "Fa32ke",
                                        "gender": "female"
                                    }
                                ],
                                "title": "Adherent 50 Fa50ke (+1 candidat)"
                            },
                            "total": 1,
                            "rate": 50
                        },
                        {
                            "candidate_group": {
                                "elected": false,
                                "candidates": [
                                    {
                                        "first_name": "Adherent 48",
                                        "last_name": "Fa48ke",
                                        "gender": "female"
                                    },
                                    {
                                        "first_name": "Adherent 50",
                                        "last_name": "Fa50ke",
                                        "gender": "female"
                                    }
                                ],
                                "title": "Adherent 48 Fa48ke (+1 candidat)"
                            },
                            "total": 1,
                            "rate": 50
                        }
                    ],
                    "expressed": 2,
                    "blank": 0,
                    "participated": 3,
                    "abstentions": 1,
                    "bulletin_count": 2
                }
            ]
            """

        Examples:
            | user                            | scope                                          |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |

    Scenario Outline: As a user granted with local scope, I can view election voters
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/designations/95da3939-f178-4d67-9237-61d4ea57e33c/voters?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "first_name": "Adherent 50",
                    "last_name": "Fa50ke",
                    "postal_code": "77000",
                    "voted_at": null
                },
                {
                    "first_name": "Adherent 32",
                    "last_name": "Fa32ke",
                    "postal_code": "77000",
                    "voted_at": "@string@.isDateTime()"
                },
                {
                    "first_name": "Adherent 48",
                    "last_name": "Fa48ke",
                    "postal_code": "77000",
                    "voted_at": "@string@.isDateTime()"
                }
            ]
            """

        Examples:
            | user                            | scope                                          |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |

    Scenario Outline: As a grand user with local scope, I can cancel an election
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "PUT" request to "/api/v3/designations/7fb0693e-1dad-44c6-984b-19e99603ea2c/cancel?scope=<scope>"
        Then the response status code should be 409
        When I send a "GET" request to "/api/v3/designations/7fb0693e-1dad-44c6-984b-19e99603ea2c?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "custom_title": "Election AL - comité des 3 communes",
                "type": "committee_supervisor",
                "election_creation_date": "@string@.isDateTime()",
                "vote_start_date": "@string@.isDateTime()",
                "vote_end_date": "@string@.isDateTime()",
                "target_year": null,
                "fully_editable": false,
                "questions": [],
                "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit",
                "election_entity_identifier": "5e00c264-1d4b-43b8-862e-29edc38389b3",
                "uuid": "7fb0693e-1dad-44c6-984b-19e99603ea2c",
                "is_canceled": false,
                "created_at": "@string@.isDateTime()"
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
