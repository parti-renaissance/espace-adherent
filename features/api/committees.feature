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
                "total_items": 1,
                "items_per_page": 2,
                "count": 1,
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
                }
            ]
        }
        """
        Examples:
            | user                            | scope                           |
            | president-ad@renaissance-dev.fr | president_departmental_assembly |
            | referent@en-marche-dev.fr       | referent                        |

    Scenario: As a user granted with local scope, I can get geo zone available for a new committee
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "GET" request to "/api/v3/zone/autocomplete?scope=referent&q=Hauts&availableForCommittee=true"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        [
            {
                "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                "type": "department",
                "postal_code": [],
                "code": "92",
                "name": "Hauts-de-Seine"
            },
            {
                "uuid": "e3f0ee7b-906e-11eb-a875-0242ac150002",
                "type": "district",
                "postal_code": [],
                "code": "92-1",
                "name": "Hauts-de-Seine (1)"
            },
            {
                "uuid": "e3f0ecf7-906e-11eb-a875-0242ac150002",
                "type": "district",
                "postal_code": [],
                "code": "92-10",
                "name": "Hauts-de-Seine (10)"
            },
            {
                "uuid": "e3f0ebd6-906e-11eb-a875-0242ac150002",
                "type": "district",
                "postal_code": [],
                "code": "92-11",
                "name": "Hauts-de-Seine (11)"
            },
            {
                "uuid": "e3f0ed59-906e-11eb-a875-0242ac150002",
                "type": "district",
                "postal_code": [],
                "code": "92-12",
                "name": "Hauts-de-Seine (12)"
            },
            {
                "uuid": "e3f0eb11-906e-11eb-a875-0242ac150002",
                "type": "district",
                "postal_code": [],
                "code": "92-13",
                "name": "Hauts-de-Seine (13)"
            },
            {
                "uuid": "e3f0eb72-906e-11eb-a875-0242ac150002",
                "type": "district",
                "postal_code": [],
                "code": "92-2",
                "name": "Hauts-de-Seine (2)"
            },
            {
                "uuid": "e3f0ec36-906e-11eb-a875-0242ac150002",
                "type": "district",
                "postal_code": [],
                "code": "92-3",
                "name": "Hauts-de-Seine (3)"
            },
            {
                "uuid": "e3f0ef9d-906e-11eb-a875-0242ac150002",
                "type": "district",
                "postal_code": [],
                "code": "92-4",
                "name": "Hauts-de-Seine (4)"
            },
            {
                "uuid": "e3f0ee1a-906e-11eb-a875-0242ac150002",
                "type": "district",
                "postal_code": [],
                "code": "92-5",
                "name": "Hauts-de-Seine (5)"
            },
            {
                "uuid": "e3f0eedc-906e-11eb-a875-0242ac150002",
                "type": "district",
                "postal_code": [],
                "code": "92-6",
                "name": "Hauts-de-Seine (6)"
            },
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
        When I send a "GET" request to "/api/v3/zone/autocomplete?scope=<scope>&q=Hauts&types[]=department&availableForCommittee=true"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        [
            {
                "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                "type": "department",
                "postal_code": [],
                "code": "92",
                "name": "Hauts-de-Seine"
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
                "e3efe6fd-906e-11eb-a875-0242ac150002",
                "e3f0ee7b-906e-11eb-a875-0242ac150002"
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
        When I send a "GET" request to "/api/v3/zone/autocomplete?scope=<scope>&q=Hauts&types[]=department&availableForCommittee=true"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        []
        """
        Examples:
            | user                            | scope                           |
            | president-ad@renaissance-dev.fr | president_departmental_assembly |
            | referent@en-marche-dev.fr       | referent                        |
