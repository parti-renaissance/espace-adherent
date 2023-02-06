@api
Feature:
  In order to manager elected representatives
  As a logged-in user
  I should be able to access elected representatives API

  Scenario Outline: As a user granted with local scope, I can get elected representatives in a zone I am manager of
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    And I send a "GET" request to "/api/v3/elected_representatives?scope=<scope>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 3,
            "items_per_page": 100,
            "count": 3,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "last_name": "92",
                "first_name": "Département",
                "gender": "male",
                "contact_phone": null,
                "uuid": "0c62d201-826b-4da7-8424-e8e17935b400",
                "current_mandates": [
                    {
                        "type": "senateur",
                        "geo_zone": {
                            "uuid": "@uuid@",
                            "code": "92",
                            "name": "Hauts-de-Seine"
                        }
                    }
                ],
                "current_political_functions": []
            },
            {
                "last_name": "DUFOUR",
                "first_name": "Michelle",
                "gender": "female",
                "contact_phone": null,
                "uuid": "34b0b236-b72e-4161-8f9f-7f23f935758f",
                "current_mandates": [
                    {
                        "type": "conseiller_municipal",
                        "geo_zone": {
                            "uuid": "@uuid@",
                            "code": "200054781",
                            "name": "Métropole du Grand Paris"
                        }
                    }
                ],
                "current_political_functions": [
                    {
                        "name": "other_member"
                    }
                ]
            },
            {
                "last_name": "LOBELL",
                "first_name": "André",
                "gender": "male",
                "contact_phone": null,
                "uuid": "82ec811a-45f7-4527-97ef-3dea61af131b",
                "current_mandates": [
                    {
                        "type": "depute",
                        "geo_zone": {
                            "uuid": "@uuid@",
                            "code": "13",
                            "name": "Bouches-du-Rhône"
                        }
                    },
                    {
                        "type": "conseiller_regional",
                        "geo_zone": {
                            "uuid": "@uuid@",
                            "code": "76540",
                            "name": "Rouen"
                        }
                    }
                ],
                "current_political_functions": [
                    {
                        "name": "vice_president_of_epci"
                    },
                    {
                        "name": "mayor_assistant"
                    }
                ]
            }
        ]
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can create an elected representative in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I add "Content-Type" header equal to "application/json"
        And I send a "POST" request to "/api/v3/elected_representatives?scope=<scope>" with body:
        """
        {
            "last_name": "Doe",
            "first_name": "John",
            "gender": "male",
            "birth_date": "1990-02-02",
            "mandates": [
                {
                    "type": "conseiller_municipal",
                    "geo_zone": "e3f2cede-906e-11eb-a875-0242ac150002",
                    "begin_at": "2022-06-02",
                    "political_affiliation": "REM",
                    "political_functions": [
                        {
                            "name": "mayor_assistant",
                            "on_going": true,
                            "begin_at": "2022-06-02"
                        }
                    ]
                }
            ],
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
            "birth_date": "@string@.isDateTime()",
            "birth_place": null,
            "contact_phone": null,
            "has_followed_training": false,
            "adherent": {
                "email_address": "referent@en-marche-dev.fr",
                "phone": {
                    "country": "FR",
                    "number": "06 73 65 43 49"
                },
                "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                "first_name": "Referent",
                "last_name": "Referent"
            },
            "mandates": [
                {
                    "id": "@integer@",
                    "type": "conseiller_municipal",
                    "is_elected": false,
                    "geo_zone": {
                        "uuid": "e3f2cede-906e-11eb-a875-0242ac150002",
                        "code": "92078",
                        "name": "Villeneuve-la-Garenne"
                    },
                    "on_going": true,
                    "begin_at": "@string@.isDateTime()",
                    "finish_at": null,
                    "political_affiliation": "REM",
                    "la_r_e_m_support": null,
                    "political_functions": [
                        {
                            "id": "@integer@",
                            "name": "mayor_assistant",
                            "clarification": null,
                            "on_going": true,
                            "begin_at": "@string@.isDateTime()",
                            "finish_at": null
                        }
                    ]
                }
            ],
            "uuid": "@uuid@",
            "email_address": "referent@en-marche-dev.fr"
        }
        """
        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | referent                                       |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can update an elected representative in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I add "Content-Type" header equal to "application/json"
        And I send a "PUT" request to "/api/v3/elected_representatives/0c62d201-826b-4da7-8424-e8e17935b400?scope=<scope>" with body:
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
                    "id": 15,
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
                    "political_affiliation": "REM",
                    "la_r_e_m_support": "official",
                    "political_functions": []
                }
            ],
            "uuid": "0c62d201-826b-4da7-8424-e8e17935b400",
            "email_address": "renaissance-user-2@en-marche-dev.fr"
        }
        """
        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | referent                                       |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can get an elected representative informations in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I add "Content-Type" header equal to "application/json"
        And I send a "GET" request to "/api/v3/elected_representatives/0c62d201-826b-4da7-8424-e8e17935b400?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
            "first_name": "Département",
            "last_name": "92",
            "gender": "male",
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
                    "id": 15,
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
                    "political_affiliation": "REM",
                    "la_r_e_m_support": "official",
                    "political_functions": []
                }
            ],
            "uuid": "0c62d201-826b-4da7-8424-e8e17935b400",
            "email_address": "renaissance-user-2@en-marche-dev.fr"
        }
        """
        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | referent                                       |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
