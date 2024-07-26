@api
@renaissance
Feature:
  In order to manage general meeting reports
  As a logged-in user
  I should be able to access general meeting reports API

  Scenario Outline: As a user granted with local scope, I can get adherent formations in a zone I am manager of
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    And I send a "GET" request to "/api/v3/general_meeting_reports?scope=<scope>"
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
                "uuid": "03e43b53-8845-41e2-9603-fa8893f25ed6",
                "title": "Premier PV d'AG du 77",
                "description": "@string@",
                "date": "@string@.isDateTime()",
                "file_path": "@string@.isUrl()",
                "visibility": "local",
                "zone": {
                    "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                    "code": "77",
                    "name": "Seine-et-Marne"
                }
            },
            {
                "uuid": "060c0ac4-48cc-4235-aeb7-24b07af1252f",
                "title": "Deuxième PV d'AG du 77",
                "description": "@string@",
                "date": "@string@.isDateTime()",
                "file_path": "@string@.isUrl()",
                "visibility": "local",
                "zone": {
                    "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                    "code": "77",
                    "name": "Seine-et-Marne"
                }
            }
        ]
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | president_departmental_assembly                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can create a general meeting report in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "POST" request to "/api/v3/general_meeting_reports?scope=<scope>" with body:
        """
        {
            "title": "New general meeting report",
            "description": "New general meeting report description",
            "date": "2023-03-08 18:00:00",
            "zone": "e3efe5c5-906e-11eb-a875-0242ac150002"
        }
        """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
            "uuid": "@uuid@",
            "title": "New general meeting report",
            "description": "@string@",
            "date": "@string@.isDateTime()",
            "file_path": null,
            "visibility": "local",
            "zone": {
                "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                "code": "77",
                "name": "Seine-et-Marne"
            }
        }
        """
        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                                       |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can update a general meeting report in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        And I send a "PUT" request to "/api/v3/general_meeting_reports/060c0ac4-48cc-4235-aeb7-24b07af1252f?scope=<scope>" with body:
        """
        {
            "title": "Deuxième PV d'AG du 77 (edited)",
            "description": "General meeting report description (edited)",
            "date": "2023-03-08 17:00:00",
            "zone": "e3efe5c5-906e-11eb-a875-0242ac150002"
        }
        """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
            "uuid": "@uuid@",
            "title": "Deuxième PV d'AG du 77 (edited)",
            "description": "General meeting report description (edited)",
            "date": "@string@.isDateTime()",
            "file_path": "@string@.isUrl()",
            "visibility": "local",
            "zone": {
                "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                "code": "77",
                "name": "Seine-et-Marne"
            }
        }
        """
        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                                       |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
