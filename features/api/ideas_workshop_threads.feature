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
                "answer": {
                    "id": 1
                },
                "comments": {
                    "total_items": 4,
                    "items": [
                        {
                            "uuid": "ecbe9136-3dc0-477d-b817-a25878dd639a",
                            "content": "Deuxième commentaire d'un référent",
                            "author": {
                                "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                                "first_name": "Referent",
                                "last_name": "Referent"
                            },
                            "created_at":"@string@.isDateTime()",
                            "approved": false
                        },
                        {
                            "uuid": "f716d3ba-004f-4958-af26-a7b010a6d458",
                            "content": "Commentaire d'un référent",
                            "author": {
                                "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                                "first_name": "Referent",
                                "last_name": "Referent"
                            },
                            "created_at":"@string@.isDateTime()",
                            "approved": false
                        },
                        {
                            "uuid": "60123090-6cdc-4de6-9cb3-07e2ec411f2f",
                            "content": "Lorem Ipsum Commentaris",
                            "author": {
                                "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                                "first_name": "Francis",
                                "last_name": "Brioul"
                            },
                            "created_at":"@string@.isDateTime()",
                            "approved": false
                        }
                    ]
                },
                "uuid": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
                "content": "J'ouvre une discussion sur le problème.",
                "author": {
                    "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                    "first_name": "Carl",
                    "last_name": "Mirabeau"
                },
                "created_at":"@string@.isDateTime()",
                "approved": false
            },
            {
                "answer": {
                    "id": 2
                },
                "comments": {
                    "total_items": 0,
                    "items": []
                },
                "uuid": "6b077cc4-1cbd-4615-b607-c23009119406",
                "content": "J'ouvre une discussion sur la solution.",
                "author": {
                    "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                    "first_name": "Lucie",
                    "last_name": "Olivera"
                },
                "created_at":"@string@.isDateTime()",
                "approved": false
            },
            {
                "answer": {
                    "id": 3
                },
                "comments": {
                    "total_items": 3,
                    "items": [
                        {
                            "uuid": "001a53d0-1134-429c-8dc1-c57643b3f069",
                            "content": "Commentaire refusé",
                            "author": {
                                "uuid": "93de5d98-383a-4863-9f47-eb7a348873a8",
                                "first_name": "Laura",
                                "last_name": "Deloche"
                            },
                            "created_at":"@string@.isDateTime()",
                            "approved": false
                        },
                        {
                            "uuid": "3fa38c45-1122-4c48-9ada-b366b3408fec",
                            "content": "Commentaire signalé",
                            "author": {
                                "uuid": "93de5d98-383a-4863-9f47-eb7a348873a8",
                                "first_name": "Laura",
                                "last_name": "Deloche"
                            },
                            "created_at":"@string@.isDateTime()",
                            "approved": false
                        },
                        {
                            "uuid": "02bf299f-678a-4829-a6a1-241995339d8d",
                            "content": "Commentaire de Laura",
                            "author": {
                                "uuid": "93de5d98-383a-4863-9f47-eb7a348873a8",
                                "first_name": "Laura",
                                "last_name": "Deloche"
                            },
                            "created_at":"@string@.isDateTime()",
                            "approved": false
                        }
                    ]
                },
                "uuid": "a508a7c5-8b07-41f4-8515-064f674a65e8",
                "content": "J'ouvre une discussion sur la comparaison.",
                "author": {
                    "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                    "first_name": "Gisele",
                    "last_name": "Berthoux"
                },
                "created_at":"@string@.isDateTime()",
                "approved": false
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can see visible threads for an idea
    When I send a "GET" request to "/api/threads?answer.idea.uuid=e4ac3efc-b539-40ac-9417-b60df432bdc5"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 5,
            "items_per_page": 3,
            "count": 3,
            "current_page": 1,
            "last_page": 2
        },
        "items": [
            {
                "answer": {
                    "id": 1
                },
                "comments": {
                    "total_items": 4,
                    "items": [
                        {
                            "uuid": "ecbe9136-3dc0-477d-b817-a25878dd639a",
                            "content": "Deuxième commentaire d'un référent",
                            "author": {
                                "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                                "first_name": "Referent",
                                "last_name": "Referent"
                            },
                            "created_at":"@string@.isDateTime()",
                            "approved": false
                        },
                        {
                            "uuid": "f716d3ba-004f-4958-af26-a7b010a6d458",
                            "content": "Commentaire d'un référent",
                            "author": {
                                "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                                "first_name": "Referent",
                                "last_name": "Referent"
                            },
                            "created_at":"@string@.isDateTime()",
                            "approved": false
                        },
                        {
                            "uuid": "60123090-6cdc-4de6-9cb3-07e2ec411f2f",
                            "content": "Lorem Ipsum Commentaris",
                            "author": {
                                "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                                "first_name": "Francis",
                                "last_name": "Brioul"
                            },
                            "created_at":"@string@.isDateTime()",
                            "approved": false
                        }
                    ]
                },
                "uuid": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
                "content": "J'ouvre une discussion sur le problème.",
                "author": {
                    "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                    "first_name": "Carl",
                    "last_name": "Mirabeau"
                },
                "created_at":"@string@.isDateTime()",
                "approved": false
            },
            {
                "answer": {
                    "id": 2
                },
                "comments": {
                    "total_items": 0,
                    "items": []
                },
                "uuid": "6b077cc4-1cbd-4615-b607-c23009119406",
                "content": "J'ouvre une discussion sur la solution.",
                "author": {
                    "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                    "first_name": "Lucie",
                    "last_name": "Olivera"
                },
                "created_at":"@string@.isDateTime()",
                "approved": false
            },
            {
                "answer": {
                    "id": 3
                },
                "comments": {
                    "total_items": 3,
                    "items": [
                        {
                            "uuid": "001a53d0-1134-429c-8dc1-c57643b3f069",
                            "content": "Commentaire refusé",
                            "author": {
                                "uuid": "93de5d98-383a-4863-9f47-eb7a348873a8",
                                "first_name": "Laura",
                                "last_name": "Deloche"
                            },
                            "created_at":"@string@.isDateTime()",
                            "approved": false
                        },
                        {
                            "uuid": "3fa38c45-1122-4c48-9ada-b366b3408fec",
                            "content": "Commentaire signalé",
                            "author": {
                                "uuid": "93de5d98-383a-4863-9f47-eb7a348873a8",
                                "first_name": "Laura",
                                "last_name": "Deloche"
                            },
                            "created_at":"@string@.isDateTime()",
                            "approved": false
                        },
                        {
                            "uuid": "02bf299f-678a-4829-a6a1-241995339d8d",
                            "content": "Commentaire de Laura",
                            "author": {
                                "uuid": "93de5d98-383a-4863-9f47-eb7a348873a8",
                                "first_name": "Laura",
                                "last_name": "Deloche"
                            },
                            "created_at":"@string@.isDateTime()",
                            "approved": false
                        }
                    ]
                },
                "uuid": "a508a7c5-8b07-41f4-8515-064f674a65e8",
                "content": "J'ouvre une discussion sur la comparaison.",
                "author": {
                    "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                    "first_name": "Gisele",
                    "last_name": "Berthoux"
                },
                "created_at":"@string@.isDateTime()",
                "approved": false
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
       "uuid":"@uuid@",
       "content":"LOREM IPSUM",
       "author":{
          "uuid":"d4b1e7e1-ba18-42a9-ace9-316440b30fa7",
          "first_name":"Martine",
          "last_name":"Lindt"
       },
       "created_at":"@string@.isDateTime()",
       "approved": false
    }
    """

  Scenario: As a logged-in user I can not approved a thread
    Given I am logged as "carl999@example.fr"
    When I send a "PUT" request to "/api/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1/approval-toggle"
    Then the response status code should be 403

  Scenario: As an idea author, I can update thread status to approved
    Given I am logged as "jacques.picard@en-marche.fr"
    And I add "Content-Type" header equal to "application/json"
    When I send a "PUT" request to "/api/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1/approval-toggle" with body:
    """
    {
        "approved": true
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "answer":{
          "id":1
       },
       "uuid":"dfd6a2f2-5579-421f-96ac-98993d0edea1",
       "content":"J'ouvre une discussion sur le probl\u00e8me.",
       "author":{
          "uuid":"e6977a4d-2646-5f6c-9c82-88e58dca8458",
          "first_name":"Carl",
          "last_name":"Mirabeau"
       },
       "created_at":"@string@.isDateTime()",
       "approved": true
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
