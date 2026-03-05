@api
@renaissance
Feature:
    In order to view chatbot thread messages
    As a logged-in user with canary tester role
    I should be able to list paginated messages of my threads

    Scenario: As a non logged-in user I can not list thread messages
        Given I send a "GET" request to "/api/v3/threads/a046adbe-57d6-4115-91bf-e8e68ef1e0fa/messages"
        Then the response status code should be 401

    Scenario: As a logged-in user without canary tester role I can not list thread messages
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/threads/a046adbe-57d6-4115-91bf-e8e68ef1e0fa/messages"
        Then the response status code should be 403

    Scenario: As a canary tester I can list my thread messages with pagination
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/threads/a046adbe-57d6-4115-91bf-e8e68ef1e0fa/messages"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "metadata": {
                    "total_items": 25,
                    "items_per_page": 20,
                    "count": 20,
                    "current_page": 1,
                    "last_page": 2
                }
            }
            """
        And the JSON node "items" should have 20 elements
        And the JSON node "items[0].uuid" should exist
        And the JSON node "items[0].role" should exist
        And the JSON node "items[0].content" should exist
        And the JSON node "items[0].date" should exist

    Scenario: As a canary tester I can access the second page of messages
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/threads/a046adbe-57d6-4115-91bf-e8e68ef1e0fa/messages?page=2"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "metadata": {
                    "total_items": 25,
                    "items_per_page": 20,
                    "count": 5,
                    "current_page": 2,
                    "last_page": 2
                }
            }
            """
        And the JSON node "items" should have 5 elements

    Scenario: As a canary tester I can not list messages of another user's thread
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/threads/b157bfcf-68e7-5226-a2c0-f9f79f020fbb/messages"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "metadata": {
                    "total_items": 0,
                    "items_per_page": 20,
                    "count": 0,
                    "current_page": 1,
                    "last_page": 1
                }
            }
            """
        And the JSON node "items" should have 0 elements

    Scenario: As a canary tester requesting a non-existent thread I get an empty result
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/threads/00000000-0000-0000-0000-000000000000/messages"
        Then the response status code should be 200
        And the JSON node "items" should have 0 elements
