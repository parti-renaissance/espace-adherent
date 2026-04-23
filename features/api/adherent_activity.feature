@api
@renaissance
Feature:
    In order to see my activity history
    As a logged-in user
    I should be able to access my activity history via API

    Scenario: As a non logged-in user I cannot get activity histories
        When I send a "GET" request to "/api/v3/adherents/e6977a4d-2646-5f6c-9c82-88e58dca8458/activity"
        Then the response status code should be 401

    Scenario: As a cadre with contacts scope I can get a militant's activity history paginated and ordered by occurredAt DESC
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents/e6977a4d-2646-5f6c-9c82-88e58dca8458/activity?scope=president_departmental_assembly"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "metadata": {
                    "total_items": 3,
                    "items_per_page": 30,
                    "count": 3,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "uuid": "b7b8e3a1-0001-0001-0001-000000000001",
                        "source_type": "hit",
                        "event_type": "open",
                        "occurred_at": "@string@.isDateTime()",
                        "metadata": {
                            "source": "page_events",
                            "object_type": "event",
                            "object_id": "abc123",
                            "button_name": null,
                            "target_url": null
                        }
                    },
                    {
                        "uuid": "b7b8e3a1-0001-0001-0001-000000000002",
                        "source_type": "hit",
                        "event_type": "activity_session",
                        "occurred_at": "@string@.isDateTime()",
                        "metadata": {
                            "source": null,
                            "object_type": null,
                            "object_id": null,
                            "button_name": null,
                            "target_url": null
                        }
                    },
                    {
                        "uuid": "b7b8e3a1-0001-0001-0001-000000000003",
                        "source_type": "action_history",
                        "event_type": "login_success",
                        "occurred_at": "@string@.isDateTime()",
                        "metadata": null
                    }
                ]
            }
            """

    Scenario: As a simple user without contacts scope I get a 403
        Given I am logged with "carl999@example.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/adherents/e6977a4d-2646-5f6c-9c82-88e58dca8458/activity"
        Then the response status code should be 403

    Scenario: As a cadre with contacts scope I get an empty list for an adherent with no activity
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents/a046adbe-9c7b-56a9-a676-6151a6785dda/activity?scope=president_departmental_assembly"
        Then the response status code should be 200
        And the JSON nodes should match:
            | metadata.total_items | 0 |
            | metadata.count       | 0 |
