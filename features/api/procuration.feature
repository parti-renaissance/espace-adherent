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
                        "accept_vote_nearby": false,
                        "vote_zone": {
                            "uuid": "@uuid@",
                            "type": "city",
                            "code": "92024",
                            "name": "Clichy",
                            "created_at": "@string@.isDateTime()"
                        },
                        "district": null,
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
                        "tags": [
                            {
                                "code": "externe",
                                "label": "Externe",
                                "type": "externe"
                            }
                        ],
                        "vote_place_name": "Bureau de vote CLICHY 1",
                        "proxy_slots": [
                            {
                                "uuid": "@uuid@",
                                "created_at": "@string@.isDateTime()",
                                "matched_at": "@string@.isDateTime()",
                                "matcher": {
                                    "uuid": "@uuid@",
                                    "first_name": "Referent",
                                    "last_name": "Referent"
                                },
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
                                    "uuid": "@uuid@"
                                },
                                "manual": false,
                                "actions": [
                                    {
                                        "uuid": "@uuid@",
                                        "status": "match",
                                        "date": "@string@.isDateTime()",
                                        "context": [],
                                        "author": {
                                            "uuid": "@uuid@",
                                            "first_name": "Referent",
                                            "last_name": "Referent"
                                        },
                                        "author_scope": "Président d'assemblée départementale"
                                    },
                                    {
                                        "uuid": "@uuid@",
                                        "status": "unmatch",
                                        "date": "@string@.isDateTime()",
                                        "context": [],
                                        "author": {
                                            "uuid": "@uuid@",
                                            "first_name": "Lucie",
                                            "last_name": "Olivera"
                                        },
                                        "author_scope": "Candidate aux législatives"
                                    },
                                    {
                                        "uuid": "@uuid@",
                                        "status": "match",
                                        "date": "@string@.isDateTime()",
                                        "context": [],
                                        "author": {
                                            "uuid": "@uuid@",
                                            "first_name": "Lucie",
                                            "last_name": "Olivera"
                                        },
                                        "author_scope": "Candidate aux législatives"
                                    }
                                ]
                            }
                        ],
                        "actions": [
                            {
                                "uuid": "@uuid@",
                                "status": "status_update",
                                "date": "@string@.isDateTime()",
                                "context": {
                                    "new_status": "pending",
                                    "old_status": "excluded"
                                },
                                "author": {
                                    "uuid": "@uuid@",
                                    "first_name": "Referent",
                                    "last_name": "Referent"
                                },
                                "author_scope": "Président d'assemblée départementale"
                            },
                            {
                                "uuid": "@uuid@",
                                "status": "status_update",
                                "date": "@string@.isDateTime()",
                                "context": {
                                    "new_status": "excluded",
                                    "old_status": "pending"
                                },
                                "author": {
                                    "uuid": "@uuid@",
                                    "first_name": "Lucie",
                                    "last_name": "Olivera"
                                },
                                "author_scope": "Candidate aux législatives"
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
                        "accept_vote_nearby": false,
                        "vote_zone": {
                            "uuid": "@uuid@",
                            "type": "city",
                            "code": "92024",
                            "name": "Clichy",
                            "created_at": "@string@"
                        },
                        "district": null,
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
                        "tags": [
                            {
                                "code": "externe",
                                "label": "Externe",
                                "type": "externe"
                            }
                        ],
                        "vote_place_name": "Bureau de vote CLICHY 1",
                        "proxy_slots": [
                            {
                                "uuid": "@uuid@",
                                "created_at": "@string@.isDateTime()",
                                "matched_at": null,
                                "matcher": null,
                                "round": {
                                    "uuid": "@uuid@",
                                    "created_at": "@string@.isDateTime()",
                                    "name": "Premier tour",
                                    "date": "@string@.isDateTime()"
                                },
                                "request": null,
                                "manual": false,
                                "actions": []
                            },
                            {
                                "uuid": "@uuid@",
                                "created_at": "@string@.isDateTime()",
                                "matched_at": null,
                                "matcher": null,
                                "round": {
                                    "uuid": "@uuid@",
                                    "created_at": "@string@.isDateTime()",
                                    "name": "Deuxième tour",
                                    "date": "@string@.isDateTime()"
                                },
                                "request": null,
                                "manual": false,
                                "actions": []
                            }
                        ],
                        "actions": []
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
                        "accept_vote_nearby": false,
                        "vote_zone": {
                            "uuid": "@uuid@",
                            "type": "city",
                            "code": "92024",
                            "name": "Clichy",
                            "created_at": "@string@"
                        },
                        "district": null,
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
                        "tags": [
                            {
                                "code": "externe",
                                "label": "Externe",
                                "type": "externe"
                            }
                        ],
                        "vote_place_name": "Bureau de vote CLICHY 1",
                        "proxy_slots": [
                            {
                                "uuid": "@uuid@",
                                "created_at": "@string@.isDateTime()",
                                "matched_at": null,
                                "matcher": null,
                                "round": {
                                    "uuid": "@uuid@",
                                    "created_at": "@string@.isDateTime()",
                                    "name": "Deuxième tour",
                                    "date": "@string@.isDateTime()"
                                },
                                "request": null,
                                "manual": false,
                                "actions": []
                            }
                        ],
                        "actions": []
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
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
                        "district": null,
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
                        "tags": [
                            {
                                "code": "externe",
                                "label": "Externe",
                                "type": "externe"
                            }
                        ],
                        "vote_place_name": "Bureau de vote CLICHY 1",
                        "from_france": true,
                        "available_proxies_count": 3,
                        "request_slots": [
                            {
                                "uuid": "@uuid@",
                                "created_at": "@string@.isDateTime()",
                                "matched_at": null,
                                "matcher": null,
                                "round": {
                                    "uuid": "@uuid@",
                                    "created_at": "@string@.isDateTime()",
                                    "name": "Premier tour",
                                    "date": "@string@.isDateTime()"
                                },
                                "proxy": null,
                                "manual": false,
                                "actions": []
                            },
                            {
                                "uuid": "@uuid@",
                                "created_at": "@string@.isDateTime()",
                                "matched_at": null,
                                "matcher": null,
                                "round": {
                                    "uuid": "@uuid@",
                                    "created_at": "@string@.isDateTime()",
                                    "name": "Deuxième tour",
                                    "date": "@string@.isDateTime()"
                                },
                                "proxy": null,
                                "manual": false,
                                "actions": []
                            }
                        ],
                        "actions": []
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
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
        And the JSON should be a superset of:
            """
            {
                "uuid": "b024ff2a-c74b-442c-8339-7df9d0c104b6",
                "manual": true,
                "actions": [
                    {
                        "uuid": "@uuid@",
                        "status": "status_update",
                        "date": "@string@.isDateTime()",
                        "author": {
                            "uuid": "@uuid@",
                            "first_name": "<first_name>",
                            "last_name": "<last_name>"
                        },
                        "author_scope": "Président d'assemblée départementale",
                        "context": {
                            "old_status": "pending",
                            "new_status": "manual"
                        }
                    }
                ]
            }
            """
        When I send a "PUT" request to "/api/v3/procuration/proxy_slots/b024ff2a-c74b-442c-8339-7df9d0c104b6?scope=<scope>" with body:
            """
            {
                "manual": false
            }
            """
        Then the response status code should be 200
        And the JSON should be a superset of:
            """
            {
                "uuid": "b024ff2a-c74b-442c-8339-7df9d0c104b6",
                "manual": false,
                "actions": [
                    {
                        "uuid": "@uuid@",
                        "status": "status_update",
                        "date": "@string@.isDateTime()",
                        "author": {
                            "uuid": "@uuid@",
                            "first_name": "<first_name>",
                            "last_name": "<last_name>"
                        },
                        "author_scope": "Président d'assemblée départementale",
                        "context": {
                            "old_status": "manual",
                            "new_status": "pending"
                        }
                    },
                    {
                        "uuid": "@uuid@",
                        "status": "status_update",
                        "date": "@string@.isDateTime()",
                        "author": {
                            "uuid": "@uuid@",
                            "first_name": "<first_name>",
                            "last_name": "<last_name>"
                        },
                        "author_scope": "Président d'assemblée départementale",
                        "context": {
                            "old_status": "pending",
                            "new_status": "manual"
                        }
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          | first_name | last_name     |
            | referent@en-marche-dev.fr | president_departmental_assembly                | Referent   | Referent      |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 | Bob        | Senateur (59) |

    Scenario Outline: As a referent I can update request slots status
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "PUT" request to "/api/v3/procuration/request_slots/f406fc52-248b-4e30-bcb6-355516a45ad9?scope=<scope>" with body:
            """
            {
                "manual": true
            }
            """
        Then the response status code should be 200
        And the JSON should be a superset of:
            """
            {
                "uuid": "f406fc52-248b-4e30-bcb6-355516a45ad9",
                "manual": true,
                "actions": [
                    {
                        "uuid": "@uuid@",
                        "status": "status_update",
                        "date": "@string@.isDateTime()",
                        "author": {
                            "uuid": "@uuid@",
                            "first_name": "<first_name>",
                            "last_name": "<last_name>"
                        },
                        "author_scope": "Président d'assemblée départementale",
                        "context": {
                            "old_status": "pending",
                            "new_status": "manual"
                        }
                    }
                ]
            }
            """

        When I send a "PUT" request to "/api/v3/procuration/request_slots/f406fc52-248b-4e30-bcb6-355516a45ad9?scope=<scope>" with body:
            """
            {
                "manual": false
            }
            """
        Then the response status code should be 200
        And the JSON should be a superset of:
            """
            {
                "uuid": "f406fc52-248b-4e30-bcb6-355516a45ad9",
                "manual": false,
                "actions": [
                    {
                        "uuid": "@uuid@",
                        "status": "status_update",
                        "date": "@string@.isDateTime()",
                        "author": {
                            "uuid": "@uuid@",
                            "first_name": "<first_name>",
                            "last_name": "<last_name>"
                        },
                        "author_scope": "Président d'assemblée départementale",
                        "context": {
                            "old_status": "manual",
                            "new_status": "pending"
                        }
                    },
                    {
                        "uuid": "@uuid@",
                        "status": "status_update",
                        "date": "@string@.isDateTime()",
                        "author": {
                            "uuid": "@uuid@",
                            "first_name": "<first_name>",
                            "last_name": "<last_name>"
                        },
                        "author_scope": "Président d'assemblée départementale",
                        "context": {
                            "old_status": "pending",
                            "new_status": "manual"
                        }
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          | first_name | last_name     |
            | referent@en-marche-dev.fr | president_departmental_assembly                | Referent   | Referent      |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 | Bob        | Senateur (59) |

    Scenario Outline: As a referent I can match and unmatch slots
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"

        # 1. Ensure request with uuid "5bc0b6e2-7073-4572-8d98-f5b64d591ca7" has a free slot for round "edf49758-c047-472d-9a98-4d24fbc58190"
        When I send a "GET" request to "/api/v3/procuration/requests/5bc0b6e2-7073-4572-8d98-f5b64d591ca7?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "request_slots": [
                    {
                        "round": {
                            "uuid": "edf49758-c047-472d-9a98-4d24fbc58190"
                        },
                        "proxy": null,
                        "actions": []
                    }
                ]
            }
            """

        # 2. Ensute proxy with uuid "c1ddce73-84dd-45a0-8eed-2078a3de8625" is available for matching
        When I send a "GET" request to "/api/v3/procuration/requests/5bc0b6e2-7073-4572-8d98-f5b64d591ca7/proxies?scope=<scope>&round=edf49758-c047-472d-9a98-4d24fbc58190"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "items": [
                    {},
                    {
                        "uuid": "c1ddce73-84dd-45a0-8eed-2078a3de8625",
                        "proxy_slots": [
                            {
                                "round": {
                                    "uuid": "edf49758-c047-472d-9a98-4d24fbc58190"
                                },
                                "request": null,
                                "actions": []
                            }
                        ]
                    }
                ]
            }
            """

        # 3. Match request and proxy for given round
        When I send a "POST" request to "/api/v3/procuration/requests/5bc0b6e2-7073-4572-8d98-f5b64d591ca7/match?scope=<scope>" with body:
            """
            {
                "round": "edf49758-c047-472d-9a98-4d24fbc58190",
                "proxy": "c1ddce73-84dd-45a0-8eed-2078a3de8625",
                "email_copy": true
            }
            """
        Then the response status code should be 200

        # 4. Ensure request has now a matched slot with given proxy for given round
        When I send a "GET" request to "/api/v3/procuration/requests/5bc0b6e2-7073-4572-8d98-f5b64d591ca7?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "request_slots": [
                    {
                        "round": {
                            "uuid": "edf49758-c047-472d-9a98-4d24fbc58190"
                        },
                        "proxy": {
                            "uuid": "c1ddce73-84dd-45a0-8eed-2078a3de8625"
                        },
                        "actions": [
                            {
                                "status": "match",
                                "date": "@string@.isDateTime()",
                                "author": {
                                    "uuid": "@uuid@",
                                    "first_name": "<first_name>",
                                    "last_name": "<last_name>"
                                },
                                "author_scope": "Président d'assemblée départementale",
                                "context": []
                            }
                        ]
                    }
                ]
            }
            """

        # 5. Unmatch request and proxy for given round
        When I send a "POST" request to "/api/v3/procuration/requests/5bc0b6e2-7073-4572-8d98-f5b64d591ca7/unmatch?scope=<scope>" with body:
            """
            {
                "round": "edf49758-c047-472d-9a98-4d24fbc58190",
                "email_copy": true
            }
            """
        Then the response status code should be 200

        # 6. Ensure request has now a free slot for given round
        When I send a "GET" request to "/api/v3/procuration/requests/5bc0b6e2-7073-4572-8d98-f5b64d591ca7?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "request_slots": [
                    {
                        "round": {
                            "uuid": "edf49758-c047-472d-9a98-4d24fbc58190"
                        },
                        "proxy": null,
                        "actions": [
                            {
                                "status": "unmatch",
                                "date": "@string@.isDateTime()",
                                "author": {
                                    "uuid": "@uuid@",
                                    "first_name": "<first_name>",
                                    "last_name": "<last_name>"
                                },
                                "author_scope": "Président d'assemblée départementale",
                                "context": []
                            },
                            {
                                "status": "match",
                                "date": "@string@.isDateTime()",
                                "author": {
                                    "uuid": "@uuid@",
                                    "first_name": "<first_name>",
                                    "last_name": "<last_name>"
                                },
                                "author_scope": "Président d'assemblée départementale",
                                "context": []
                            }
                        ]
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          | first_name | last_name     |
            | referent@en-marche-dev.fr | president_departmental_assembly                | Referent   | Referent      |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 | Bob        | Senateur (59) |

    Scenario Outline: As a referent I can match and unmatch slots
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"

        # 1. Ensure request with uuid "5bc0b6e2-7073-4572-8d98-f5b64d591ca7" has a pending status
        When I send a "GET" request to "/api/v3/procuration/requests/5bc0b6e2-7073-4572-8d98-f5b64d591ca7?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "status": "pending",
                "actions": []
            }
            """

        # 2. Update request status to "excluded"
        When I send a "PATCH" request to "/api/v3/procuration/requests/5bc0b6e2-7073-4572-8d98-f5b64d591ca7?scope=<scope>" with body:
            """
            {
                "status": "excluded"
            }
            """
        Then the response status code should be 200

        # 3. Ensure request has now status set to "excluded"
        When I send a "GET" request to "/api/v3/procuration/requests/5bc0b6e2-7073-4572-8d98-f5b64d591ca7?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "status": "excluded",
                "actions": [
                    {
                        "status": "status_update",
                        "date": "@string@",
                        "author": {
                            "uuid": "@uuid@",
                            "first_name": "<first_name>",
                            "last_name": "<last_name>"
                        },
                        "author_scope": "Président d'assemblée départementale",
                        "context": {
                            "old_status": "pending",
                            "new_status": "excluded"
                        }
                    }
                ]
            }
            """

        # 4. Update request status to "pending"
        When I send a "PATCH" request to "/api/v3/procuration/requests/5bc0b6e2-7073-4572-8d98-f5b64d591ca7?scope=<scope>" with body:
            """
            {
                "status": "pending"
            }
            """
        Then the response status code should be 200

        # 5. Ensure request has now status set to "pending"
        When I send a "GET" request to "/api/v3/procuration/requests/5bc0b6e2-7073-4572-8d98-f5b64d591ca7?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "status": "pending",
                "actions": [
                    {
                        "status": "status_update",
                        "date": "@string@",
                        "author": {
                            "uuid": "@uuid@",
                            "first_name": "<first_name>",
                            "last_name": "<last_name>"
                        },
                        "author_scope": "Président d'assemblée départementale",
                        "context": {
                            "old_status": "excluded",
                            "new_status": "pending"
                        }
                    },
                    {
                        "status": "status_update",
                        "date": "@string@",
                        "author": {
                            "uuid": "@uuid@",
                            "first_name": "<first_name>",
                            "last_name": "<last_name>"
                        },
                        "author_scope": "Président d'assemblée départementale",
                        "context": {
                            "old_status": "pending",
                            "new_status": "excluded"
                        }
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          | first_name | last_name     |
            | referent@en-marche-dev.fr | president_departmental_assembly                | Referent   | Referent      |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 | Bob        | Senateur (59) |
