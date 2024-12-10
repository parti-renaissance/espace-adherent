@api
@renaissance
Feature:
    In order to manage department sites
    As a logged-in user
    I should be able to access department sites API

    Scenario Outline: As Anonymous user I cannot access department sites endpoints
        When I send a "<method>" request to "<url>"
        Then the response status code should be 401

        Examples:
            | method | url                                                           |
            | GET    | /api/v3/department_sites                                      |
            | POST   | /api/v3/department_sites                                      |
            | GET    | /api/v3/department_sites/51e507e5-3d7c-4f08-b05d-b7cb45e960d3 |
            | PUT    | /api/v3/department_sites/51e507e5-3d7c-4f08-b05d-b7cb45e960d3 |

    Scenario Outline: as a logged-in user without correct right I cannot access department sites endpoints
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "<method>" request to "<url>"
        Then the response status code should be 403

        Examples:
            | method | url                                                                                                 |
            | GET    | /api/v3/department_sites?scope=president_departmental_assembly                                      |
            | POST   | /api/v3/department_sites?scope=president_departmental_assembly                                      |
            | GET    | /api/v3/department_sites/51e507e5-3d7c-4f08-b05d-b7cb45e960d3?scope=president_departmental_assembly |
            | PUT    | /api/v3/department_sites/51e507e5-3d7c-4f08-b05d-b7cb45e960d3?scope=president_departmental_assembly |

    Scenario: As referent I cannot create my department site if no scope
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/department_sites"
        Then the response status code should be 403

    Scenario: As a referent I cannot create my department site with a different scope than referent
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/department_sites?scope=deputy"
        Then the response status code should be 403

    Scenario: As a referent I cannot create a department site other than my department one
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/department_sites?scope=president_departmental_assembly" with body:
            """
            {
                "content": "<p>test</p>",
                "json_content": "{\"test\": \"test\"}",
                "zone": "e3efe563-906e-11eb-a875-0242ac150002"
            }
            """
        Then the response status code should be 400
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "zone",
                        "message": "Cette zone ne fait pas partie des zones que vous gérez."
                    }
                ]
            }
            """

    Scenario: As a referent I can create my department site
        Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/department_sites?scope=president_departmental_assembly" with body:
            """
            {
                "content": "<p>test</p>",
                "json_content": "{\"test\": \"test\"}",
                "zone": "e3efe563-906e-11eb-a875-0242ac150002"
            }
            """
        Then the response status code should be 201
        And the JSON should be equal to:
            """
            {
                "uuid": "@uuid@",
                "slug": "75-paris",
                "url": "@string@.isUrl()"
            }
            """

    Scenario: As referent I can get my department site
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/department_sites?scope=president_departmental_assembly"
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
                        "uuid": "51e507e5-3d7c-4f08-b05d-b7cb45e960d3",
                        "slug": "92-hauts-de-seine",
                        "url": "@string@.isUrl()",
                        "zone": {
                            "code": "92",
                            "name": "Hauts-de-Seine",
                            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
                        }
                    }
                ]
            }
            """

    Scenario: As referent I cannot create my department site with invalid payload
        Given I am logged with "referent-75-77@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/department_sites?scope=president_departmental_assembly" with body:
            """
            {}
            """
        Then the response status code should be 400
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "zone",
                        "message": "Le type de la zone est invalide."
                    },
                    {
                        "propertyPath": "zone",
                        "message": "Cette valeur ne doit pas être vide."
                    },
                    {
                        "propertyPath": "content",
                        "message": "Cette valeur ne doit pas être vide."
                    }
                ]
            }
            """

    Scenario: As a referent I cannot create my department site
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "PUT" request to "/api/v3/department_sites/51e507e5-3d7c-4f08-b05d-b7cb45e960d3?scope=president_departmental_assembly" with body:
            """
            {
                "content": "<p>ceci est une mise à jour</p>",
                "json_content": "{\"maj\": \"maj\"}",
                "zone": "e3efe6fd-906e-11eb-a875-0242ac150002"
            }
            """
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "uuid": "@uuid@",
                "slug": "92-hauts-de-seine",
                "url": "@string@.isUrl()"
            }
            """

    Scenario: As a referent I can get my department site
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/department_sites/51e507e5-3d7c-4f08-b05d-b7cb45e960d3?scope=president_departmental_assembly"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "content": "@string@",
                "json_content": "@string@",
                "uuid": "51e507e5-3d7c-4f08-b05d-b7cb45e960d3",
                "slug": "92-hauts-de-seine",
                "url": "@string@.isUrl()",
                "zone": {
                    "code": "92",
                    "name": "Hauts-de-Seine",
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
                }
            }
            """
