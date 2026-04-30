@api
@renaissance
Feature:
    In order to see my activity history
    As a logged-in user
    I should be able to access my activity history via API

    Scenario: As a non logged-in user I cannot get activity histories
        When I send a "GET" request to "/api/v3/adherents/a046adbe-9c7b-56a9-a676-6151a6785dda/activity"
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
                    "total_items": "@integer@.greaterThan(0)",
                    "items_per_page": 30,
                    "count": "@integer@.greaterThan(0)",
                    "current_page": 1,
                    "last_page": "@integer@.greaterThan(0)"
                },
                "items": [
                    {
                        "uuid": "@uuid@",
                        "source_type": "@string@",
                        "event_type": "@string@",
                        "occurred_at": "@string@.isDateTime()"
                    }
                ]
            }
            """

    Scenario: As a cadre with contacts scope I can filter activity history by event type
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents/e6977a4d-2646-5f6c-9c82-88e58dca8458/activity?scope=president_departmental_assembly&eventType=open"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "metadata": {
                    "total_items": "@integer@.greaterThan(0)",
                    "count": "@integer@.greaterThan(0)"
                }
            }
            """
        And the JSON node "items" should contain an element with "event_type" equal to "open"

    Scenario: As a cadre with contacts scope I can combine source type and event type filters
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents/e6977a4d-2646-5f6c-9c82-88e58dca8458/activity?scope=president_departmental_assembly&sourceType=hit&eventType=activity_session"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "metadata": {
                    "total_items": "@integer@.greaterThan(0)",
                    "count": "@integer@.greaterThan(0)"
                }
            }
            """
        And the JSON node "items[0].event_type" should be equal to "activity_session"
        And the JSON node "items[0].source_type" should be equal to "hit"

    Scenario: A correspondent cannot read activity history of an adherent outside their zone
        Given I am logged with "je-mengage-user-1@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents/a9fc8d48-6f57-4d89-ae73-50b3f9b586f4/activity?scope=correspondent"
        Then the response status code should be 403

    Scenario: An Agora President cannot read activity of an adherent who is not member of their agora
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        # michelle.dufour is President of agora-1; adherent a9fc8d48 is NOT a member and not in Michelle's geographic zone (CH).
        When I send a "GET" request to "/api/v3/adherents/a9fc8d48-6f57-4d89-ae73-50b3f9b586f4/activity?scope=agora_president"
        Then the response status code should be 403

    Scenario: An Agora President can read activity history of a member of their agora
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        # adherent e6977a4d is member of agora-1; access works via agora membership (not via geo zone).
        When I send a "GET" request to "/api/v3/adherents/e6977a4d-2646-5f6c-9c82-88e58dca8458/activity?scope=agora_president"
        Then the response status code should be 200

    Scenario: A request to /activity with an unknown adherent UUID returns 404 (not 403)
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        # The UUID is well-formed but does not match any adherent in DB.
        When I send a "GET" request to "/api/v3/adherents/00000000-0000-4000-8000-000000000000/activity?scope=president_departmental_assembly"
        Then the response status code should be 404
