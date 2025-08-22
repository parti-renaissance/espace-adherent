@api
@renaissance
Feature:
    In order to manage elected representatives
    As a logged-in user
    I should be able to access elected representatives API

    Scenario Outline: As a user granted with local scope, I can get elected representatives in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/elected_representatives?scope=<scope>&page_size=2"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 6,
                    "items_per_page": 2,
                    "count": 2,
                    "current_page": 1,
                    "last_page": 3
                },
                "items": [
                    {
                        "last_name": "92",
                        "first_name": "Département",
                        "gender": "male",
                        "contributed_at": "@string@.isDateTime()",
                        "contribution_status": "eligible",
                        "last_contribution": {
                            "end_date": null,
                            "start_date": "@string@.isDateTime()",
                            "status": "active",
                            "type": "mandate",
                            "uuid": "117921c2-93ce-4307-8364-709fd34de79c"
                        },
                        "contact_phone": null,
                        "uuid": "0c62d201-826b-4da7-8424-e8e17935b400",
                        "current_mandates": [
                            {
                                "type": "senateur",
                                "geo_zone": {
                                    "uuid": "@uuid@",
                                    "code": "92",
                                    "name": "Hauts-de-Seine"
                                },
                                "uuid": "9051e0b5-4b56-41b9-8657-cc45e431c727"
                            }
                        ],
                        "current_political_functions": []
                    },
                    {
                        "last_name": "BOUILLOUX",
                        "first_name": "Delphine",
                        "gender": "female",
                        "contributed_at": null,
                        "contribution_status": null,
                        "last_contribution": null,
                        "contact_phone": "+33 9 99 88 77 66",
                        "uuid": "4b8bb9fd-0645-47fd-bb9a-3515bf46618a",
                        "current_mandates": [
                            {
                                "type": "conseiller_municipal",
                                "geo_zone": {
                                    "uuid": "@uuid@",
                                    "code": "92024",
                                    "name": "Clichy"
                                },
                                "uuid": "34d7b4b1-67e9-48fd-b193-373f5076e3f2"
                            }
                        ],
                        "current_political_functions": [
                            {
                                "name": "mayor"
                            }
                        ]
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can create an elected representative in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/elected_representatives?scope=<scope>" with body:
            """
            {
                "last_name": "Doe",
                "first_name": "John",
                "gender": "male",
                "birth_date": "1990-02-02",
                "adherent": "29461c49-2646-4d89-9c82-50b3f9b586f4"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "last_name": "Doe",
                "first_name": "John",
                "gender": "male",
                "contributed_at": null,
                "contribution_status": null,
                "last_contribution": null,
                "birth_date": "@string@.isDateTime()",
                "birth_place": null,
                "contact_phone": null,
                "has_followed_training": false,
                "adherent": {
                    "email_address": "referent@en-marche-dev.fr",
                    "phone": "+33 6 73 65 43 49",
                    "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                    "first_name": "Referent",
                    "last_name": "Referent"
                },
                "mandates": [],
                "uuid": "@uuid@",
                "email_address": "referent@en-marche-dev.fr",
                "payments": []
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can update an elected representative in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "PUT" request to "/api/v3/elected_representatives/0c62d201-826b-4da7-8424-e8e17935b400?scope=<scope>" with body:
            """
            {
                "last_name": "Doe",
                "first_name": "Jane"
            }
            """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "last_name": "Doe",
                "first_name": "Jane",
                "gender": "male",
                "contributed_at": "@string@.isDateTime()",
                "contribution_status": "eligible",
                "last_contribution": {
                    "end_date": null,
                    "start_date": "@string@.isDateTime()",
                    "status": "active",
                    "type": "mandate",
                    "uuid": "117921c2-93ce-4307-8364-709fd34de79c"
                },
                "birth_date": "@string@.isDateTime()",
                "birth_place": null,
                "contact_phone": null,
                "has_followed_training": false,
                "adherent": {
                    "email_address": "renaissance-user-2@en-marche-dev.fr",
                    "phone": null,
                    "uuid": "d0a0935f-da7c-4caa-b582-a8c2376e5158",
                    "first_name": "John",
                    "last_name": "Smith"
                },
                "mandates": [
                    {
                        "uuid": "9051e0b5-4b56-41b9-8657-cc45e431c727",
                        "type": "senateur",
                        "is_elected": true,
                        "geo_zone": {
                            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                            "code": "92",
                            "name": "Hauts-de-Seine"
                        },
                        "on_going": true,
                        "begin_at": "@string@.isDateTime()",
                        "finish_at": null,
                        "political_affiliation": null,
                        "la_r_e_m_support": "official",
                        "political_functions": []
                    }
                ],
                "uuid": "0c62d201-826b-4da7-8424-e8e17935b400",
                "email_address": "renaissance-user-2@en-marche-dev.fr",
                "payments": [
                    {
                        "date": "@string@.isDateTime()",
                        "method": "IBAN",
                        "status": "confirmed",
                        "amount": 50,
                        "uuid": "@uuid@"
                    },
                    {
                        "date": "@string@.isDateTime()",
                        "method": "IBAN",
                        "status": "confirmed",
                        "amount": 50,
                        "uuid": "@uuid@"
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can get an elected representative informations in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/elected_representatives/0c62d201-826b-4da7-8424-e8e17935b400?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "first_name": "Département",
                "last_name": "92",
                "gender": "male",
                "contributed_at": "@string@.isDateTime()",
                "contribution_status": "eligible",
                "last_contribution": {
                    "end_date": null,
                    "start_date": "@string@.isDateTime()",
                    "status": "active",
                    "type": "mandate",
                    "uuid": "117921c2-93ce-4307-8364-709fd34de79c"
                },
                "birth_date": "@string@.isDateTime()",
                "birth_place": null,
                "contact_phone": null,
                "has_followed_training": false,
                "adherent": {
                    "email_address": "renaissance-user-2@en-marche-dev.fr",
                    "phone": null,
                    "uuid": "d0a0935f-da7c-4caa-b582-a8c2376e5158",
                    "first_name": "John",
                    "last_name": "Smith"
                },
                "mandates": [
                    {
                        "uuid": "9051e0b5-4b56-41b9-8657-cc45e431c727",
                        "type": "senateur",
                        "is_elected": true,
                        "geo_zone": {
                            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                            "code": "92",
                            "name": "Hauts-de-Seine"
                        },
                        "on_going": true,
                        "begin_at": "@string@.isDateTime()",
                        "finish_at": null,
                        "political_affiliation": null,
                        "la_r_e_m_support": "official",
                        "political_functions": []
                    }
                ],
                "uuid": "0c62d201-826b-4da7-8424-e8e17935b400",
                "email_address": "renaissance-user-2@en-marche-dev.fr",
                "payments": [
                    {
                        "date": "@string@.isDateTime()",
                        "method": "IBAN",
                        "status": "confirmed",
                        "amount": 50,
                        "uuid": "@uuid@"
                    },
                    {
                        "date": "@string@.isDateTime()",
                        "method": "IBAN",
                        "status": "confirmed",
                        "amount": 50,
                        "uuid": "@uuid@"
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can delete an elected representative created by me
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "DELETE" request to "/api/v3/elected_representatives/0c62d201-826b-4da7-8424-e8e17935b400?scope=<scope>"
        Then the response status code should be 204

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can get an elected representative mandate informations
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/elected_mandates/9051e0b5-4b56-41b9-8657-cc45e431c727?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "type": "senateur",
                "is_elected": true,
                "geo_zone": {
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                    "code": "92",
                    "name": "Hauts-de-Seine"
                },
                "on_going": true,
                "begin_at": "2019-01-11T00:00:00+01:00",
                "finish_at": null,
                "political_affiliation": null,
                "la_r_e_m_support": "official",
                "elected_representative": {
                    "last_name": "92",
                    "first_name": "Département",
                    "uuid": "0c62d201-826b-4da7-8424-e8e17935b400"
                },
                "political_functions": [],
                "uuid": "9051e0b5-4b56-41b9-8657-cc45e431c727"
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can create an elected representative mandate informations
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/elected_mandates?scope=<scope>" with body:
            """
            {
                "type": "membre_EPCI",
                "is_elected": true,
                "geo_zone": "e3efe6fd-906e-11eb-a875-0242ac150002",
                "on_going": true,
                "begin_at": "2023-01-13",
                "finish_at": null,
                "political_affiliation": null,
                "la_r_e_m_support": "informal",
                "elected_representative": "0c62d201-826b-4da7-8424-e8e17935b400",
                "political_functions": [
                    {
                        "name": "president_of_epci",
                        "clarification": "string",
                        "on_going": true,
                        "begin_at": "2023-01-30T12:01:51.575Z",
                        "finish_at": null
                    },
                    {
                        "name": "vice_president_of_epci",
                        "clarification": "string",
                        "on_going": false,
                        "begin_at": "2023-01-13T12:01:51.575Z",
                        "finish_at": "2023-01-30T11:01:51.575Z"
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
              "type": "membre_EPCI",
              "is_elected": true,
              "geo_zone": {
                "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                "code": "92",
                "name": "Hauts-de-Seine"
              },
              "on_going": true,
              "begin_at": "2023-01-13T00:00:00+01:00",
              "finish_at": null,
              "political_affiliation": null,
              "la_r_e_m_support": "informal",
              "elected_representative": {
                "last_name": "92",
                "first_name": "Département",
                "uuid": "0c62d201-826b-4da7-8424-e8e17935b400"
              },
              "political_functions": [
                {
                  "id": @integer@,
                  "name": "president_of_epci",
                  "clarification": "string",
                  "on_going": true,
                  "begin_at": "2023-01-30T12:01:51+00:00",
                  "finish_at": null
                },
                {
                  "id": @integer@,
                  "name": "vice_president_of_epci",
                  "clarification": "string",
                  "on_going": false,
                  "begin_at": "2023-01-13T12:01:51+00:00",
                  "finish_at": "2023-01-30T11:01:51+00:00"
                }
              ],
              "uuid": "@uuid@"
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can update an elected representative mandate informations
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "PUT" request to "/api/v3/elected_mandates/9051e0b5-4b56-41b9-8657-cc45e431c727?scope=<scope>" with body:
            """
            {
                "type": "membre_EPCI",
                "is_elected": true,
                "geo_zone": "e3efe6fd-906e-11eb-a875-0242ac150002",
                "on_going": true,
                "begin_at": "2023-01-13T00:00:00+01:00",
                "finish_at": null,
                "political_affiliation": null,
                "la_r_e_m_support": "informal",
                "elected_representative": "0c62d201-826b-4da7-8424-e8e17935b400",
                "political_functions": [
                    {
                        "name": "president_of_epci",
                        "clarification": "test",
                        "on_going": true,
                        "begin_at": "2023-01-30T12:01:51+00:00",
                        "finish_at": null
                    },
                    {
                        "name": "vice_president_of_epci",
                        "clarification": "test",
                        "on_going": false,
                        "begin_at": "2023-01-13T12:01:51+00:00",
                        "finish_at": "2023-01-30T11:01:51+00:00"
                    }
                ]
            }
            """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
              "type": "membre_EPCI",
              "is_elected": true,
              "geo_zone": {
                "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                "code": "92",
                "name": "Hauts-de-Seine"
              },
              "on_going": true,
              "begin_at": "2023-01-13T00:00:00+01:00",
              "finish_at": null,
              "political_affiliation": null,
              "la_r_e_m_support": "informal",
              "elected_representative": {
                "last_name": "92",
                "first_name": "Département",
                "uuid": "0c62d201-826b-4da7-8424-e8e17935b400"
              },
              "political_functions": [
                {
                  "id": @integer@,
                  "name": "president_of_epci",
                  "clarification": "test",
                  "on_going": true,
                  "begin_at": "2023-01-30T12:01:51+00:00",
                  "finish_at": null
                },
                {
                  "id": @integer@,
                  "name": "vice_president_of_epci",
                  "clarification": "test",
                  "on_going": false,
                  "begin_at": "2023-01-13T12:01:51+00:00",
                  "finish_at": "2023-01-30T11:01:51+00:00"
                }
              ],
              "uuid": "9051e0b5-4b56-41b9-8657-cc45e431c727"
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can delete an elected representative mandate informations
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "DELETE" request to "/api/v3/elected_mandates/9051e0b5-4b56-41b9-8657-cc45e431c727?scope=<scope>"
        Then the response status code should be 204

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
