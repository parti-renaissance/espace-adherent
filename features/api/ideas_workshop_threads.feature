@api
Feature:
  In order to create and delete threads
  As a user
  I should be able to access API threads

  Background:
    Given the following fixtures are loaded:
      | LoadIdeaAnswerData        |
      | LoadIdeaThreadData        |
      | LoadIdeaThreadCommentData |

  Scenario: As a non logged-in user I can see visibled threads paginated
    When I send a "GET" request to "/api/threads?page=1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 6,
            "items_per_page": 3,
            "count": 3,
            "current_page": 1,
            "last_page": 2
        },
        "items": [
            {
                "uuid": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
                "answer": {
                    "id": 1
                },
                "content": "J'ouvre une discussion sur le problème.",
                "author": {
                    "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                    "first_name": "Carl",
                    "last_name": "Mirabeau"
                },
                "created_at": "@string@.isDateTime()"
            },
            {
                "uuid": "6b077cc4-1cbd-4615-b607-c23009119406",
                "answer": {
                    "id": @integer@
                },
                "content": "J'ouvre une discussion sur la solution.",
                "author": {
                    "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                    "first_name": "Lucie",
                    "last_name": "Olivera"
                },
                "created_at": "@string@.isDateTime()"
            },
            {
                "uuid": "a508a7c5-8b07-41f4-8515-064f674a65e8",
                "answer": {
                    "id": @integer@
                },
                "content": "J'ouvre une discussion sur la comparaison.",
                "author": {
                    "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                    "first_name": "Gisele",
                    "last_name": "Berthoux"
                },
                "created_at": "@string@.isDateTime()"
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can see visibled threads for an idea
    When I send a "GET" request to "/api/threads?answer.idea.uuid=3b1ea810-115f-4b2c-944d-34a55d7b7e4d"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 3,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "7857957c-2044-4469-bd9f-04a60820c8bd",
                "answer": {
                    "id": 9
                },
                "content": "[Help Ecology] J'ouvre une discussion sur le problème.",
                "author": {
                    "uuid":"b4219d47-3138-5efd-9762-2ef9f9495084",
                    "first_name": "Gisele",
                    "last_name": "Berthoux"
                },
                "created_at": "@string@.isDateTime()"
            }
        ]
    }
    """

  Scenario: As a logged-in user I can add my thread to an answer
    Given I add "Content-Type" header equal to "application/json"
    And I am logged as "martine.lindt@gmail.com"
    When I send a "POST" request to "/api/threads" with body:
    """
    {
        "answer": "1",
        "content": "LOREM IPSUM"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "answer":{
          "id":1
       },
       "content":"LOREM IPSUM",
       "author":{
          "uuid":"d4b1e7e1-ba18-42a9-ace9-316440b30fa7",
          "first_name":"Martine",
          "last_name":"Lindt"
       },
       "created_at": "@string@.isDateTime()",
       "uuid":"@string@"
    }
    """

  Scenario: As a logged-in user I can not approved a thread
    Given I am logged as "carl999@example.fr"
    When I send a "PUT" request to "/api/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1/approve"
    Then the response status code should be 403

  Scenario: As an idea author, I can update thread status to approved
    Given I am logged as "jacques.picard@en-marche.fr"
    When I send a "PUT" request to "/api/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1/approve"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "answer":{
          "id":1
       },
       "content":"J'ouvre une discussion sur le problème.",
       "author":{
          "uuid":"e6977a4d-2646-5f6c-9c82-88e58dca8458",
          "first_name":"Carl",
          "last_name":"Mirabeau"
       },
       "created_at": "@string@.isDateTime()",
       "uuid":"dfd6a2f2-5579-421f-96ac-98993d0edea1"
    }
    """

  Scenario: As a non logged-in user I can not delete a thread
    When I send a "DELETE" request to "/api/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1"
    Then the response status code should be 401

  Scenario: As a logged-in user I can not delete a thread that is not mine
    Given I am logged as "jacques.picard@en-marche.fr"
    When I send a "DELETE" request to "/api/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1"
    Then the response status code should be 403

  Scenario: As a logged-in user I can delete my thread
    Given I am logged as "carl999@example.fr"
    When I send a "DELETE" request to "/api/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1"
    Then the response status code should be 204
    And the response should be empty
