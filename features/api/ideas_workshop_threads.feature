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
    When I send a "GET" request to "/api/ideas-workshop/threads?page=1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 8,
            "items_per_page": 3,
            "count": 3,
            "current_page": 1,
            "last_page": 3
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
                            "uuid": "02bf299f-678a-4829-a6a1-241995339d8d",
                            "content": "Deuxième commentaire d'un référent",
                            "author": {
                                "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                                "nickname":null,
                                "first_name": "Referent",
                                "last_name": "R."
                            },
                            "approved": false,
                            "created_at": "@string@.isDateTime()"
                        },
                        {
                            "uuid": "f716d3ba-004f-4958-af26-a7b010a6d458",
                            "content": "Commentaire d'un référent",
                            "author": {
                                "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                                "nickname":null,
                                "first_name": "Referent",
                                "last_name": "R."
                            },
                            "approved": false,
                            "created_at": "@string@.isDateTime()"
                        },
                        {
                            "uuid": "60123090-6cdc-4de6-9cb3-07e2ec411f2f",
                            "content": "Lorem Ipsum Commentaris",
                            "author": {
                                "uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                                "nickname":null,
                                "first_name": "Francis",
                                "last_name": "B."
                            },
                            "approved": false,
                            "created_at": "@string@.isDateTime()"
                        }
                    ]
                },
                "uuid": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
                "content": "J'ouvre une discussion sur le problème.",
                "author": {
                    "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                    "nickname":"pont",
                    "first_name": null,
                    "last_name": null
                },
                "approved": false,
                "created_at": "@string@.isDateTime()"
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
                    "nickname":null,
                    "first_name": "Lucie",
                    "last_name": "O."
                },
                "approved": false,
                "created_at": "@string@.isDateTime()"
            },
            {
                "answer": {
                    "id": 3
                },
                "comments": {
                    "total_items": 4,
                    "items": [
                        {
                            "uuid": "37116c8b-a36e-4a0d-8346-baba91cd1330",
                            "content": "Commentaire de l'adhérent à desadhérer",
                            "author": {
                                "uuid": "46ab0600-b5a0-59fc-83a7-cc23ca459ca0",
                                "nickname":null,
                                "first_name": "Michel",
                                "last_name": "V."
                            },
                            "approved": false,
                            "created_at": "@string@.isDateTime()"
                        },
                        {
                            "uuid": "ecbe9136-3dc0-477d-b817-a25878dd639a",
                            "content": "<p>Commentaire signalé</p>",
                            "author": {
                                "uuid": "93de5d98-383a-4863-9f47-eb7a348873a8",
                                "nickname":null,
                                "first_name": "Laura",
                                "last_name": "D."
                            },
                            "approved": false,
                            "created_at": "@string@.isDateTime()"
                        },
                        {
                            "uuid": "3fa38c45-1122-4c48-9ada-b366b3408fec",
                            "content": "<p>Commentaire non approuvé</p>",
                            "author": {
                                "uuid": "93de5d98-383a-4863-9f47-eb7a348873a8",
                                "nickname":null,
                                "first_name": "Laura",
                                "last_name": "D."
                            },
                            "approved": false,
                            "created_at": "@string@.isDateTime()"
                        }
                    ]
                },
                "uuid": "a508a7c5-8b07-41f4-8515-064f674a65e8",
                "content": "J'ouvre une discussion sur la comparaison.",
                "author": {
                    "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                    "nickname":null,
                    "first_name": "Gisele",
                    "last_name": "B."
                },
                "approved": false,
                "created_at": "@string@.isDateTime()"
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can see visible threads for an answer
    When I send a "GET" request to "/api/ideas-workshop/threads?answer.id=3"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 3,
            "items_per_page": 3,
            "count": 3,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "answer": {
                    "id": 3
                },
                "comments": {
                    "total_items": 4,
                    "items": [
                        {
                            "uuid": "37116c8b-a36e-4a0d-8346-baba91cd1330",
                            "content": "Commentaire de l'adhérent à desadhérer",
                            "author": {
                                "uuid": "46ab0600-b5a0-59fc-83a7-cc23ca459ca0",
                                "nickname":null,
                                "first_name": "Michel",
                                "last_name": "V."
                            },
                            "approved": false,
                            "created_at": "@string@.isDateTime()"
                        },
                        {
                            "uuid": "ecbe9136-3dc0-477d-b817-a25878dd639a",
                            "content": "<p>Commentaire signalé</p>",
                            "author": {
                                "uuid": "93de5d98-383a-4863-9f47-eb7a348873a8",
                                "nickname":null,
                                "first_name": "Laura",
                                "last_name": "D."
                            },
                            "approved": false,
                            "created_at": "@string@.isDateTime()"
                        },
                        {
                            "uuid": "3fa38c45-1122-4c48-9ada-b366b3408fec",
                            "content": "<p>Commentaire non approuvé</p>",
                            "author": {
                                "uuid": "93de5d98-383a-4863-9f47-eb7a348873a8",
                                "nickname":null,
                                "first_name": "Laura",
                                "last_name": "D."
                            },
                            "approved": false,
                            "created_at": "@string@.isDateTime()"
                        }
                    ]
                },
                "uuid": "a508a7c5-8b07-41f4-8515-064f674a65e8",
                "content": "J'ouvre une discussion sur la comparaison.",
                "author": {
                    "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                    "nickname":null,
                    "first_name": "Gisele",
                    "last_name": "B."
                },
                "approved": false,
                "created_at": "@string@.isDateTime()"
            },
            {
                "answer": {
                    "id": 3
                },
                "comments": {
                    "total_items": 0,
                    "items": []
                },
                "uuid": "78d7daa1-657c-4e7e-87bc-24eb4ea26ea2",
                "content": "Une nouvelle discussion.",
                "author": {
                    "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                    "nickname":null,
                    "first_name": "Gisele",
                    "last_name": "B."
                },
                "approved": false,
                "created_at": "@string@.isDateTime()"
            },
            {
                "answer": {
                    "id": 3
                },
                "comments": {
                    "total_items": 0,
                    "items": []
                },
                "uuid": "b191f13a-5a05-49ed-8ec3-c335aa68f439",
                "content": "Une discussion signalée.",
                "author": {
                    "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                    "nickname":null,
                    "first_name": "Gisele",
                    "last_name": "B."
                },
                "approved": false,
                "created_at": "@string@.isDateTime()"
            }
        ]
    }
    """

  Scenario: As a logged-in user I can add my thread to an answer
    Given I add "Content-Type" header equal to "application/json"
    And I am logged as "martine.lindt@gmail.com"
    When I send a "POST" request to "/api/ideas-workshop/threads" with body:
    """
    {
        "answer": "1",
        "content": "LOREM IPSUM🤘"
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
          "nickname":null,
          "first_name":"Martine",
          "last_name":"Lindt"
       },
       "created_at":"@string@.isDateTime()",
       "approved": false
    }
    """

  Scenario Outline: As a logged-in user I can not approve/disapprove other threads
    Given I am logged as "carl999@example.fr"
    When I send a "PUT" request to "<url>"
    Then the response status code should be 403
    Examples:
      | url                                                                         |
      | /api/ideas-workshop/threads/6b077cc4-1cbd-4615-b607-c23009119406/approve    |
      | /api/ideas-workshop/threads/6b077cc4-1cbd-4615-b607-c23009119406/disapprove |

  Scenario: As an idea author, I can update thread status to approved
    Given I am logged as "jacques.picard@en-marche.fr"
    And I add "Content-Type" header equal to "application/json"
    When I send a "PUT" request to "/api/ideas-workshop/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1/approve"
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
          "nickname":"pont",
          "first_name":null,
          "last_name":null
       },
       "created_at":"@string@.isDateTime()",
       "approved": true
    }
    """
    When I send a "PUT" request to "/api/ideas-workshop/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1/disapprove"
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
          "nickname":"pont",
          "first_name":null,
          "last_name":null
       },
       "created_at":"@string@.isDateTime()",
       "approved": false
    }
    """

  Scenario: As a non logged-in user I can not delete a thread
    When I send a "DELETE" request to "/api/ideas-workshop/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1"
    Then the response status code should be 401

  Scenario: As a logged-in user I can not delete a thread that is not mine
    Given I am logged as "jacques.picard@en-marche.fr"
    When I send a "DELETE" request to "/api/ideas-workshop/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1"
    Then the response status code should be 403

  Scenario: As a logged-in user I can delete my thread
    Given I am logged as "carl999@example.fr"
    When I send a "DELETE" request to "/api/ideas-workshop/threads/dfd6a2f2-5579-421f-96ac-98993d0edea1"
    Then the response status code should be 204
    And the response should be empty
