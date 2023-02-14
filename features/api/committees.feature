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
