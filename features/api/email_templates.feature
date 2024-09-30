@api
@renaissance
Feature:
    In order to manage email templates
    As client software developer
    I should be able to access API email templates

    Scenario: As a user granted with local scope, I can get the list of my templates
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/email_templates?scope=president_departmental_assembly"
        Then the response status code should be 200
        And the response should be in JSON
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
                        "label": "Campagne national d'adhésion",
                        "uuid": "ba5a7294-f7a6-4710-88c8-9ceb67ad61ce",
                        "created_at": "@string@.isDateTime()",
                        "from_admin": true
                    },
                    {
                        "label": "Campaign Newsletter 92",
                        "uuid": "825c3c30-f4bd-42b5-8adf-29926a02a4af",
                        "created_at": "@string@.isDateTime()",
                        "from_admin": false
                    }
                ]
            }
            """

    Scenario: As a user granted with local scope, I can get a template
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/email_templates/825c3c30-f4bd-42b5-8adf-29926a02a4af?scope=president_departmental_assembly"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "label": "Campaign Newsletter 92",
                "subject": null,
                "subject_editable": true,
                "content": "@string@",
                "json_content": "@string@",
                "uuid": "825c3c30-f4bd-42b5-8adf-29926a02a4af",
                "created_at": "@string@.isDateTime()",
                "from_admin": false
            }
            """

    Scenario: As a user granted with local scope, I can create a template
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/email_templates?scope=president_departmental_assembly" with body:
            """
            {
                "label": "Campaign adhésion 2023",
                "content": "<p>test</p>",
                "json_content": "{\"test\": \"test\"}"
            }
            """
        Then the response status code should be 201
        And the JSON should be equal to:
            """
            {
                "uuid": "@uuid@"
            }
            """

    Scenario: As a user granted with local scope, I can update my own template
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "PUT" request to "/api/v3/email_templates/825c3c30-f4bd-42b5-8adf-29926a02a4af?scope=president_departmental_assembly" with body:
            """
            {
                "label": "Campaign Newsletter",
                "content": "<p>test</p>",
                "json_content": "{\"test\": \"test\"}"
            }
            """
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "uuid": "825c3c30-f4bd-42b5-8adf-29926a02a4af"
            }
            """

    Scenario: As a user granted with local scope, I cannot update an admin template
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "PUT" request to "/api/v3/email_templates/ba5a7294-f7a6-4710-88c8-9ceb67ad61ce?scope=president_departmental_assembly" with body:
            """
            {
                "label": "Campagne national d'adhésion",
                "content": "<p>test</p>",
                "json_content": "{\"test\": \"test\"}"
            }
            """
        Then the response status code should be 403
