@api
Feature:
  In order to see comments
  As a user
  I should be able to access API threads and thread comments

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData          |
      | LoadIdeaData              |
      | LoadIdeaThreadData        |
      | LoadIdeaThreadCommentData |

  Scenario: As a non logged-in user I can see visibled thread comments paginated
    When I send a "GET" request to "/api/thread_comments?page=1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 6,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 3
        },
        "items": [
            {
                "uuid": "b99933f3-180c-4248-82f8-1b0eb950740d",
                "thread": {
                    "uuid": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
                    "answer": {
                        "id": 1
                    },
                    "content": "J'ouvre une discussion sur le problème.",
                    "author": {
                        "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                        "first_name": "Carl",
                        "last_name": "Mirabeau"
                    }
                },
                "content": "Aenean viverra efficitur lorem",
                "author": {
                    "uuid": "acc73b03-9743-47d8-99db-5a6c6f55ad67",
                    "first_name": "Benjamin",
                    "last_name": "Duroc"
                }
            },
            {
                "uuid": "60123090-6cdc-4de6-9cb3-07e2ec411f2f",
                "thread": {
                    "uuid": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
                    "answer": {
                        "id": 1
                    },
                    "content": "J'ouvre une discussion sur le problème.",
                    "author": {
                        "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                        "first_name": "Carl",
                        "last_name": "Mirabeau"
                    }
                },
                "content": "Lorem Ipsum Commentaris",
                "author": {
                    "uuid":"a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                    "first_name": "Francis",
                    "last_name": "Brioul"
                }
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can see visibled thread comments for an idea
    When I send a "GET" request to "/api/threads/1/comments"
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
                "uuid": "b99933f3-180c-4248-82f8-1b0eb950740d",
                "thread": {
                    "uuid": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
                    "answer": {
                        "id": 1
                    },
                    "content": "J'ouvre une discussion sur le problème.",
                    "author": {
                        "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                        "first_name": "Carl",
                        "last_name": "Mirabeau"
                    }
                },
                "content": "Aenean viverra efficitur lorem",
                "author": {
                    "uuid": "acc73b03-9743-47d8-99db-5a6c6f55ad67",
                    "first_name": "Benjamin",
                    "last_name": "Duroc"
                }
            },
            {
                "uuid": "60123090-6cdc-4de6-9cb3-07e2ec411f2f",
                "thread": {
                    "uuid": "dfd6a2f2-5579-421f-96ac-98993d0edea1",
                    "answer": {
                        "id": 1
                    },
                    "content": "J'ouvre une discussion sur le problème.",
                    "author": {
                        "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                        "first_name": "Carl",
                        "last_name": "Mirabeau"
                    }
                },
                "content": "Lorem Ipsum Commentaris",
                "author": {
                    "uuid":"a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                    "first_name": "Francis",
                    "last_name": "Brioul"
                }
            }
        ]
    }
    """

  Scenario: As a logged-in user I can add my comment to a thread
    Given I add "Content-Type" header equal to "application/json"
    And I am logged as "martine.lindt@gmail.com"
    When I send a "POST" request to "/api/thread_comments" with body:
    """
    {
      "thread": 1,
      "content": "Phasellus vitae enim faucibus"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "thread":{
          "answer":{
             "id":1
          },
          "content":"J'ouvre une discussion sur le probl\u00e8me.",
          "author":{
             "uuid":"e6977a4d-2646-5f6c-9c82-88e58dca8458",
             "first_name":"Carl",
             "last_name":"Mirabeau"
          },
          "uuid":"dfd6a2f2-5579-421f-96ac-98993d0edea1"
       },
       "content":"Phasellus vitae enim faucibus",
       "author":{
          "uuid":"d4b1e7e1-ba18-42a9-ace9-316440b30fa7",
          "first_name":"Martine",
          "last_name":"Lindt"
       },
       "uuid": "@uuid@"
    }
    """

  Scenario: As a non logged-in user I can not report a comment
    When I send a "PUT" request to "/api/thread_comments/1/report"
    Then the response status code should be 401

  Scenario: As a logged-in user I can not approve a comment
    Given I am logged as "carl999@example.fr"
    When I send a "PUT" request to "/api/thread_comments/1/approve"
    Then the response status code should be 403

  Scenario: As an idea author, I can approve my comment
    Given I am logged as "jacques.picard@en-marche.fr"
    When I send a "PUT" request to "/api/thread_comments/1/approve"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "thread":{
          "answer":{
             "id":1
          },
          "content":"J'ouvre une discussion sur le probl\u00e8me.",
          "author":{
             "uuid":"e6977a4d-2646-5f6c-9c82-88e58dca8458",
             "first_name":"Carl",
             "last_name":"Mirabeau"
          },
          "uuid":"dfd6a2f2-5579-421f-96ac-98993d0edea1"
       },
       "content":"Aenean viverra efficitur lorem",
       "author":{
          "uuid":"acc73b03-9743-47d8-99db-5a6c6f55ad67",
          "first_name":"Benjamin",
          "last_name":"Duroc"
       },
       "uuid":"b99933f3-180c-4248-82f8-1b0eb950740d"
    }
    """

  Scenario: As an comment author, I can not report my comment
    Given I am logged as "benjyd@aol.com"
    When I send a "PUT" request to "/api/thread_comments/1/report"
    Then the response status code should be 403

  Scenario: As a logged-in user I can report a comment
    Given I am logged as "benjyd@aol.com"
    When I send a "PUT" request to "/api/thread_comments/2/report"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "thread":{
          "answer":{
             "id":1
          },
          "content":"J'ouvre une discussion sur le probl\u00e8me.",
          "author":{
             "uuid":"e6977a4d-2646-5f6c-9c82-88e58dca8458",
             "first_name":"Carl",
             "last_name":"Mirabeau"
          },
          "uuid":"@uuid@"
       },
       "content":"Lorem Ipsum Commentaris",
       "author":{
          "uuid":"a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
          "first_name":"Francis",
          "last_name":"Brioul"
       },
       "uuid":"@uuid@"
    }
    """

  Scenario: As a non logged-in user I can not delete a comment
    When I send a "DELETE" request to "/api/thread_comments/1"
    Then the response status code should be 401

  Scenario: As a logged-in user I can not delete a comment that is not mine
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "DELETE" request to "/api/thread_comments/1"
    Then the response status code should be 403

  Scenario: As a logged-in user I can delete my comment
    When I am logged as "benjyd@aol.com"
    And I send a "DELETE" request to "/api/thread_comments/1"
    Then the response status code should be 204
    And the response should be empty
