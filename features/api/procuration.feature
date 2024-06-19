@api
@renaissance
Feature:
  In order to get and manipulate proxies
  As a client of different apps
  I should be able to access proxies API

  Scenario Outline: As a referent I can get a list of proxies corresponding to my zones
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/procuration/proxies?scope=<scope>&page_size=2"
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
                "slots": 1,
                "status": "pending",
                "gender": "male",
                "first_names": "John, Patrick",
                "last_name": "Durand",
                "email": "john.durand@test.dev",
                "phone": "+33 6 11 22 33 44",
                "birthdate": "1992-03-14",
                "vote_zone": {
                    "uuid": "@uuid@",
                    "type": "city",
                    "code": "92024",
                    "name": "Clichy",
                    "created_at": "@string@.isDateTime()"
                },
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "post_address": {
                    "address": "57 Boulevard de la Madeleine",
                    "postal_code": "06000",
                    "city": null,
                    "city_name": "Nice",
                    "country": "FR",
                    "additional_address": null
                },
                "age": "@number@",
                "id": "@string@",
                "tags": [{
                    "label": "Citoyen",
                    "type": "citoyen"
                }],
                "vote_place_name": "Bureau de vote CLICHY 1",
                "proxy_slots": [
                    {
                        "uuid": "@uuid@",
                        "created_at": "@string@.isDateTime()",
                        "round": {
                            "uuid": "@uuid@",
                            "created_at": "@string@.isDateTime()",
                            "name": "Premier tour",
                            "date": "@string@.isDateTime()"
                        },
                        "request": {
                            "first_names": "Jack, Didier",
                            "gender": "male",
                            "id": "@string@",
                            "last_name": "Doe",
                            "matched_at": null,
                            "matcher": null,
                            "uuid": "@uuid@"
                        }
                    }
                ]
            },
            {
                "slots": 1,
                "status": "pending",
                "gender": "male",
                "first_names": "Pierre",
                "last_name": "Durand",
                "email": "pierre.durand@test.dev",
                "phone": "+33 6 11 22 33 44",
                "birthdate": "@string@",
                "vote_zone": {
                    "uuid": "@uuid@",
                    "type": "city",
                    "code": "92024",
                    "name": "Clichy",
                    "created_at": "@string@"
                },
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "post_address": {
                    "address": "57 Boulevard de la Madeleine",
                    "postal_code": "06000",
                    "city": null,
                    "city_name": "Nice",
                    "country": "FR",
                    "additional_address": null
                },
                "age": "@number@",
                "id": "@string@",
                "tags": [{
                    "label": "Citoyen",
                    "type": "citoyen"
                }],
                "vote_place_name": "Bureau de vote CLICHY 1",
                "proxy_slots": [
                    {
                        "uuid": "@uuid@",
                        "created_at": "@string@.isDateTime()",
                        "round": {
                            "uuid": "@uuid@",
                            "created_at": "@string@.isDateTime()",
                            "name": "Premier tour",
                            "date": "@string@.isDateTime()"
                        },
                        "request": null
                    },
                    {
                        "uuid": "@uuid@",
                        "created_at": "@string@.isDateTime()",
                        "round": {
                            "uuid": "@uuid@",
                            "created_at": "@string@.isDateTime()",
                            "name": "Deuxième tour",
                            "date": "@string@.isDateTime()"
                        },
                        "request": null
                    }
                ]
            }
        ]
    }
    """
    When I send a "GET" request to "/api/v3/procuration/proxies?scope=<scope>&page_size=2&search=Jacques%20Dur"
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
                "slots": 1,
                "status": "pending",
                "gender": "male",
                "first_names": "Jacques, Charles",
                "last_name": "Durand",
                "email": "jacques.durand@test.dev",
                "phone": "+33 6 11 22 33 44",
                "birthdate": "@string@",
                "vote_zone": {
                    "uuid": "@uuid@",
                    "type": "city",
                    "code": "92024",
                    "name": "Clichy",
                    "created_at": "@string@"
                },
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "post_address": {
                    "address": "57 Boulevard de la Madeleine",
                    "postal_code": "06000",
                    "city": null,
                    "city_name": "Nice",
                    "country": "FR",
                    "additional_address": null
                },
                "age": "@number@",
                "id": "@string@",
                "tags": [{
                    "label": "Citoyen",
                    "type": "citoyen"
                }],
                "vote_place_name": "Bureau de vote CLICHY 1",
                "proxy_slots": [
                    {
                        "uuid": "@uuid@",
                        "created_at": "@string@.isDateTime()",
                        "round": {
                            "uuid": "@uuid@",
                            "created_at": "@string@.isDateTime()",
                            "name": "Deuxième tour",
                            "date": "@string@.isDateTime()"
                        },
                        "request": null
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

    Scenario Outline: As a referent I can get a list of requests corresponding to my zones
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/procuration/requests?scope=<scope>&page_size=1&search=chris"
        Then the response status code should be 200
        And the JSON should be equal to:
        """
        {
            "metadata": {
                "total_items": 1,
                "items_per_page": 1,
                "count": 1,
                "current_page": 1,
                "last_page": 1
            },
            "items": [
                {
                    "status": "pending",
                    "gender": "male",
                    "first_names": "Chris",
                    "last_name": "Doe",
                    "birthdate": "@string@.isDateTime()",
                    "vote_zone": {
                        "uuid": "@uuid@",
                        "type": "city",
                        "code": "92024",
                        "name": "Clichy",
                        "created_at": "@string@.isDateTime()"
                    },
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "post_address": {
                        "address": "58 Boulevard de la Madeleine",
                        "postal_code": "06000",
                        "city": null,
                        "city_name": "Nice",
                        "country": "FR",
                        "additional_address": null
                    },
                    "age": "@number@",
                    "id": "@string@",
                    "tags": [{
                        "label": "Citoyen",
                        "type": "citoyen"
                    }],
                    "vote_place_name": "Bureau de vote CLICHY 1",
                    "from_france": true,
                    "available_proxies_count": 3,
                    "matched_at": null,
                    "matcher": null,
                    "request_slots": [
                        {
                            "uuid": "@uuid@",
                            "created_at": "@string@.isDateTime()",
                            "round": {
                                "uuid": "@uuid@",
                                "created_at": "@string@.isDateTime()",
                                "name": "Premier tour",
                                "date": "@string@.isDateTime()"
                            },
                            "proxy": null
                        },
                        {
                            "uuid": "@uuid@",
                            "created_at": "@string@.isDateTime()",
                            "round": {
                                "uuid": "@uuid@",
                                "created_at": "@string@.isDateTime()",
                                "name": "Deuxième tour",
                                "date": "@string@.isDateTime()"
                            },
                            "proxy": null
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

    Scenario Outline: As a referent I can update proxy slots status
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "PUT" request to "/api/v3/procuration/proxy_slots/b024ff2a-c74b-442c-8339-7df9d0c104b6?scope=<scope>" with body:
        """
        {
            "manual": true
        }
        """
        Then the response status code should be 200
        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | referent                                       |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a referent I can update request slots status
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "PUT" request to "/api/v3/procuration/request_slots/f406fc52-248b-4e30-bcb6-355516a45ad9?scope=<scope>" with body:
        """
        {
            "manual": true
        }
        """
        Then the response status code should be 200
        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | referent                                       |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
