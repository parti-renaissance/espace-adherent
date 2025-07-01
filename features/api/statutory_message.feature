@api
@renaissance
Feature:
    I should be able to create and send statutory message

    Scenario Outline: As a logged-in user I can get statutory templates
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/email_templates?statutory=1&scope=<scope>"
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
                        "label": "Template email statutaire",
                        "uuid": "@string@",
                        "created_at": "@string@.isDateTime()",
                        "from_admin": true
                    }
                ]
            }
            """

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | referent@en-marche-dev.fr       | president_departmental_assembly                |
            | senateur@en-marche-dev.fr       | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a logged-in user I can retrieve my statutory messages
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherent_messages?statutory=1&scope=president_departmental_assembly"
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
                        "uuid": "@string@",
                        "created_at": "@string@.isDateTime()",
                        "synchronized": true,
                        "preview_link": null,
                        "author": {
                            "uuid": "@string@",
                            "first_name": "Damien",
                            "last_name": "Durock"
                        },
                        "label": "@string@",
                        "subject": "@string@",
                        "status": "draft",
                        "sent_at": null,
                        "recipient_count": 10,
                        "source": "api",
                        "from_name": "Damien Durock | Renaissance",
                        "statistics": {
                            "sent": 0,
                            "opens": 0,
                            "open_rate": 0,
                            "clicks": 0,
                            "click_rate": 0,
                            "unsubscribe": 0,
                            "unsubscribe_rate": 0
                        }
                    },
                    {
                        "uuid": "@string@",
                        "created_at": "@string@.isDateTime()",
                        "synchronized": true,
                        "preview_link": null,
                        "author": {
                            "uuid": "@string@",
                            "first_name": "Damien",
                            "last_name": "Durock"
                        },
                        "label": "@string@",
                        "subject": "@string@",
                        "status": "sent",
                        "sent_at": "@string@.isDateTime()",
                        "recipient_count": 2,
                        "source": "api",
                        "from_name": "Damien Durock | Renaissance",
                        "statistics": {
                            "sent": 0,
                            "opens": 0,
                            "open_rate": 0,
                            "clicks": 0,
                            "click_rate": 0,
                            "unsubscribe": 0,
                            "unsubscribe_rate": 0
                        }
                    }
                ]
            }
            """

    Scenario: As a logged-in user I can send a statutory message
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/adherent_messages?scope=president_departmental_assembly" with body:
            """
            {
                "type": "statutory",
                "label": "Message statutaire de test",
                "subject": "Voici un message statutaire",
                "content": "<table><tr><td><strong>Hello</strong></td></tr></table>",
                "json_content": "{\"foo\": \"bar\", \"items\": [1, 2, true, \"hello world\"]}"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "@string@",
                "synchronized": true,
                "label": "Message statutaire de test",
                "subject": "Voici un message statutaire",
                "status": "draft",
                "recipient_count": 3,
                "preview_link": null,
                "source": "api",
                "author": {
                    "uuid": "@uuid@",
                    "first_name": "Damien",
                    "last_name": "Durock",
                    "email_address": "president-ad@renaissance-dev.fr"
                },
                "updated_at": "@string@.isDateTime()"
            }
            """
        When I save this response
        And I send a "POST" request to "/api/v3/adherent_messages/:last_response.uuid:/send-test?scope=president_departmental_assembly"
        Then the response status code should be 200
        And the response should be equal to
            """
            "OK"
            """
        When I send a "POST" request to "/api/v3/adherent_messages/:saved_response.uuid:/send?scope=president_departmental_assembly"
        Then the response status code should be 200
        And the response should be equal to
            """
            "OK"
            """
